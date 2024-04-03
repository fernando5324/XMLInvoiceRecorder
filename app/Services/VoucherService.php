<?php

namespace App\Services;

use App\Events\Vouchers\VouchersCreated;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SimpleXMLElement;

class VoucherService
{
    const message_error = 'Error inesperado. Comuníquese con el administrador';

    private static function getDataResponse($message, $data)
    {
        return (object) [
            "message" => $message,
            "data" => $data,
        ];
    }

    public function getVouchers(int $page, int $paginate): LengthAwarePaginator
    {
        return Voucher::with(['lines', 'user'])->paginate(perPage: $paginate, page: $page);
    }

    public function deleteVoucher(string $id)
    {
        $ldate = date('Y-m-d H:i:s');
        $message = "";
        $voucher = Voucher::find($id);

        try {
            if ($voucher != null) {
                $voucher->deleted_at = $ldate;
                $voucher->save();

                $voucher_line = VoucherLine::where('voucher_id', $id)->get();

                foreach ($voucher_line as $line) {
                    $line->deleted_at = $ldate;
                    $line->save();
                }
            } else {
                $message = 'Comprobante ' . $id . ' no encontrado o ya fue eliminado.';
            }
            
        } catch (\Throwable $th) {
            $message = $this->message_error;
        }

        #$data = (object) ['message' => $message, 'item' => $voucher];
        return $this->getDataResponse($message, $voucher);
    }

    public function getVouchersTotalMont()
    {

        $total = Voucher::where('currency_type', 'PEN')->sum("total_amount");
        $total_usd = Voucher::where('currency_type', 'USD')->sum("total_amount");

        $result = [
            'total_pen' => $total,
            'total_usd' => $total_usd,
        ];

        return $result;
    }

    public function restoreVouchersValues()
    {
        $vouchers = Voucher::get();
        $restoredItems = [];

        foreach ($vouchers as $voucher) {
            $xml = new SimpleXMLElement($voucher->xml_content);

            $serieAndNumber = explode("-", $xml->xpath('//cbc:ID')[0]);

            $valid = false;

            if ($voucher->voucher_serie == null || $voucher->voucher_serie == "") {
                $voucher->voucher_serie = (string) $serieAndNumber[0];
                $valid = true;
            }

            if ($voucher->voucher_number == null || $voucher->voucher_number == "") {
                $voucher->voucher_number = (string) $serieAndNumber[1];
                $valid = true;
            }

            if ($voucher->voucher_type == null || $voucher->voucher_type == "") {
                $voucher->voucher_type = (string) $xml->xpath('//cbc:InvoiceTypeCode')[0];
                $valid = true;
            }

            if ($voucher->currency_type == null || $voucher->currency_type == "") {
                $voucher->currency_type = (string) $xml->xpath('//cbc:DocumentCurrencyCode')[0];
                $valid = true;
            }

            if ($valid) {
                $restoredItems[] = $voucher->id;
                $voucher->save();
            }
        }

        return $restoredItems;
    }

    public function getVoucher(string $serie, int $number, string $initial_date, string $end_date)
    {

        if ($initial_date == null || $end_date == null) {
            return Voucher::with(['lines', 'user'])->where("voucher_serie", $serie)->get();
        }

        return Voucher::with(['lines', 'user'])->where("voucher_serie", $serie)->where("voucher_number", $number)->whereBetween('created_at', [$initial_date, $end_date])->get();

    }

    /**
     * @param string[] $xmlContents
     * @param User $user
     * @return Voucher[]
     */
    public function storeVouchersFromXmlContents(array $xmlContents, User $user): array
    {
        $vouchers = [];
        foreach ($xmlContents as $xmlContent) {
            $vouchers[] = $this->storeVoucherFromXmlContent($xmlContent, $user);
        }

        VouchersCreated::dispatch($vouchers, $user);

        return $vouchers;
    }

    public function storeVoucherFromXmlContent(string $xmlContent, User $user)
    {
        $xml = new SimpleXMLElement($xmlContent);
        $message = "";

        //code...

        $issuerName = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name')[0];
        $issuerDocumentType = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
        $issuerDocumentNumber = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

        $receiverName = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0];
        $receiverDocumentType = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
        $receiverDocumentNumber = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

        $totalAmount = (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0];

        $serieAndNumber = explode("-", $xml->xpath('//cbc:ID')[0]);
        $voucherSerie = (string) $serieAndNumber[0];
        $voucherNumber = (string) $serieAndNumber[1];
        $voucherType = (string) $xml->xpath('//cbc:InvoiceTypeCode')[0];
        $currency_type = (string) $xml->xpath('//cbc:DocumentCurrencyCode')[0];

        $exists_voucher = Voucher::where('voucher_serie', $voucherSerie)->where('voucher_number', $voucherNumber)->first();

        if ($exists_voucher != null) {
            $message = 'Comprobante con la serie ' . $voucherSerie . ' repetido.';
            return $this->getDataResponse($message, []);
        }

        $voucher = new Voucher([
            'voucher_serie' => $voucherSerie,
            'voucher_number' => $voucherNumber,
            'voucher_type' => $voucherType,
            'currency_type' => $currency_type,
            'issuer_name' => $issuerName,
            'issuer_document_type' => $issuerDocumentType,
            'issuer_document_number' => $issuerDocumentNumber,
            'receiver_name' => $receiverName,
            'receiver_document_type' => $receiverDocumentType,
            'receiver_document_number' => $receiverDocumentNumber,
            'total_amount' => $totalAmount,
            'xml_content' => $xmlContent,
            'user_id' => $user->id,
        ]);

        try {

            $voucher->save();

            foreach ($xml->xpath('//cac:InvoiceLine') as $invoiceLine) {
                $name = (string) $invoiceLine->xpath('cac:Item/cbc:Description')[0];
                $quantity = (float) $invoiceLine->xpath('cbc:InvoicedQuantity')[0];
                $unitPrice = (float) $invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0];

                $voucherLine = new VoucherLine([
                    'name' => $name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'voucher_id' => $voucher->id,
                ]);

                $voucherLine->save();
            }

        } catch (\Throwable $th) {
            $message = "Comprobante " . $voucherSerie . '. ' . $this->message_error;
        }

        return $this->getDataResponse($message, $voucher);
    }
}
