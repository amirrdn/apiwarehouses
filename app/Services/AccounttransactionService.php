<?php

namespace App\Services;
use App\Models\AccountransactionModel;
/**
 * Class AccounttransactionService.
 */
class AccounttransactionService
{
    public function insert_account_transaction(object $data)
    {
        $transaction_type   = $data->transaction_type ? $data->transaction_type : '';
        $reference_table_id = $data->reference_table_id;
        $debit_account_id   = $data->debit_account_id ? $data->debit_account_id : null;
        $credit_account_id  = $data->credit_account_id ? $data->credit_account_id : null;
        $debit_amt          = $data->debit_amt ? $data->debit_amt : 0;
        $credit_amt         = $data->credit_amt ? $data->credit_amt : 0;
        $process            = $data->process;
        $note               = $data->note;
        $transaction_date   = $data->transaction_date;
        $payment_code       = $data->payment_code;
        $customer_id        = $data->customer_id ? $data->customer_id : null;
        $supplier_id        = $data->supplier_id ? $data->supplier_id : null;

        $transaction = array();

        if ($transaction_type == 'EXPENSE PAYMENT') {
            if ($process == 'UPDATE') {
                //delete previouse data of the transactions
                AccountransactionModel::where('ref_expense_id', $reference_table_id)->delete();
            }
            $transaction = array(
                "transaction_type" => $transaction_type,
                "ref_expense_id"   => $reference_table_id,
                "debit_account_id" => $debit_account_id,
                "debit_amt"        => $debit_amt,
            );
        } else if ($transaction_type == 'PURCHASE PAYMENT RETURN') {
            $transaction = array(
                "transaction_type"              => $transaction_type,
                "ref_purchasepaymentsreturn_id" => $reference_table_id,
                "credit_account_id"             => $credit_account_id,
                "credit_amt"                    => $credit_amt,
            );
        } else if ($transaction_type == 'PURCHASE PAYMENT') {
            $transaction = array(
                "transaction_type"        => $transaction_type,
                "ref_purchasepayments_id" => $reference_table_id,
                "debit_account_id"        => $debit_account_id,
                "debit_amt"               => $debit_amt,
            );
        } else if ($transaction_type == 'SALES PAYMENT RETURN') {
            $transaction = array(
                "transaction_type"           => $transaction_type,
                "ref_salespaymentsreturn_id" => $reference_table_id,
                "debit_account_id"           => $debit_account_id,
                "debit_amt"                  => $debit_amt,
            );
        } else if ($transaction_type == 'SALES PAYMENT' || $transaction_type == 'SALES PAYMENT & OB') {
            //CUSTOMER BULK PAYMENT INCLUDES OB PAYMENT
            $transaction = array(
                "transaction_type"     => $transaction_type,
                "ref_salespayments_id" => $reference_table_id,
                "credit_account_id"    => $credit_account_id,
                "credit_amt"           => $credit_amt,
            );

        } else if ($transaction_type == 'OPENING BALANCE PAID' && !empty($supplier_id)) {
            $transaction = array(
                "transaction_type"        => $transaction_type,
                "ref_purchasepayments_id" => $reference_table_id,
                "debit_account_id"        => $debit_account_id,
                "debit_amt"               => $debit_amt,
            );
        } else if ($transaction_type == 'OPENING BALANCE PAID' && !empty($customer_id)) {
            //SALES PAYMENTS
            $transaction = array(
                "transaction_type"     => $transaction_type,
                "ref_salespayments_id" => $reference_table_id,
                "credit_account_id"    => $credit_account_id,
                "credit_amt"           => $credit_amt,
            );
        } else if ($transaction_type == 'OPENING BALANCE' && empty($customer_id) && empty($supplier_id)) {
            //WHILE CREATING ACCOUNT
            $transaction = array(
                "transaction_type"  => $transaction_type,
                "ref_accounts_id"   => $reference_table_id,
                "credit_account_id" => $credit_account_id,
                "credit_amt"        => $credit_amt,
            );
        } else if ($transaction_type == 'DEPOSIT') {
            if ($process == 'UPDATE') {
                //delete previouse data of the transactions
                AccountransactionModel::where('ref_moneydeposits_id', $reference_table_id)->delete();
            }
            $transaction = array(
                "transaction_type"     => $transaction_type,
                "ref_moneydeposits_id" => $reference_table_id,
                "debit_account_id"     => $debit_account_id,
                "credit_account_id"    => $credit_account_id,
                "debit_amt"            => $debit_amt,
                "credit_amt"           => $credit_amt,
            );
        } else if ($transaction_type == 'TRANSFER') {
            if ($process == 'UPDATE') {
                //delete previouse data of the transactions
                AccountransactionModel::where('ref_moneytransfer_id', $reference_table_id)->delete();
            }
            $transaction = array(
                "transaction_type"     => $transaction_type,
                "ref_moneytransfer_id" => $reference_table_id,
                "debit_account_id"     => $debit_account_id,
                "credit_account_id"    => $credit_account_id,
                "debit_amt"            => $debit_amt,
                "credit_amt"           => $credit_amt,
            );
        } else {
            //"Invalid Transaction Type";
            return false;
        }
        $transaction['store_id']         = \Auth::user()->store_id;
        $transaction['created_by']       = \Auth::user()->username;
        $transaction['created_date']     = date("Y-m-d");
        $transaction['transaction_date'] = $transaction_date;
        $transaction['note']             = $note;
        $transaction['payment_code']     = $payment_code;
        $transaction['customer_id']      = $customer_id;
        $transaction['supplier_id']      = $supplier_id;

        AccountransactionModel::insert($transaction);
        if(!empty($debit_account_id)){
			if(!$this->update_account_balance($debit_account_id)){
				return false;
			}
		}
        if(!empty($credit_account_id)){
			if(!$this->update_account_balance($credit_account_id)){
				return false;
			}
		}
        return false;
    }
    public function update_account_balance($account_id){
        $balance = $this->get_account_balance($account_id);
        $setquery   = \DB::table('ac_accounts')->where('id', $account_id)->update(['balance' => $balance]); 
        if(!$setquery){
            return false;
        }
        return true;
    }
    public function get_account_balance($account_id){
        $debit      = AccountransactionModel::selectRaw("coalesce(sum(debit_amt),0) as debit")->where('debit_account_id', $account_id)->first()->debit;
        $credit     = AccountransactionModel::selectRaw("coalesce(sum(credit_amt),0) as credit")->where('credit_account_id',$account_id)->first()->credit;
        $balance = $credit-$debit;
        return $balance;
    }
}
