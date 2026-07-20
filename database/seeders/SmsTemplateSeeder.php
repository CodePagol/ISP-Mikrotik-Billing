<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'template_name' => 'all_customers',
                'template' => "Dear {CUSTOMER_NAME}, Your {MONTH}'s bill is {BILL_AMOUNT}/=, ID- {CUSTOMER_ID} Please pay before {LAST_DAY_OF_PAY_BILL}. -{COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_en' => "Example 1:\n</br>\n{CUSTOMER_NAME},\nThanks for being with us.\nID: {CUSTOMER_ID},\nIP {IP},\nPPPoE Username {PPPOE_USERNAME},\n- {COMPANY_NAME} {COMPANY_MOBILE}\n</br></br>\nExample 2:\n</br>\n{CUSTOMER_NAME},\nYour {MONTH}'s bill is {BILL_AMOUNT}/=,\nID- {CUSTOMER_ID}\nPlease pay before {LAST_DAY_OF_PAY_BILL}.\n-{COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_bn' => "Example 1:\n</br>\n{CUSTOMER_NAME},\nআমাদের সাথে থাকার জন্য ধন্যবাদ।\nID: {CUSTOMER_ID},\nIP {IP},\nPPPoE Username: {PPPOE_USERNAME}, \n- {COMPANY_NAME} {COMPANY_MOBILE}\n</br></br>\nExample 2:\n</br>\nআপনার NET এর, {MONTH} মাসের বিল {BILL_AMOUNT}/=,\nID- {CUSTOMER_ID}\nবিল দেয়ার শেষ দিন {LAST_DAY_OF_PAY_BILL},\n-{COMPANY_NAME}",
                'is_active' => true,
            ],
            [
                'template_name' => 'area_wise_customer_due_list',
                'template' => "Your NET service total due is {AMOUNT}Tk. ID- {CUSTOMER_ID}, Last day to pay the bill is {LAST_DAY_DUE_DATE} / {AUTO_TEMPORARY_DAY}, Please pay as soon as possible. - {COMPANY_NAME}",
                'template_ex_en' => "Your NET service total due is {AMOUNT}Tk.\nID- {CUSTOMER_ID},\nLast day to pay the bill is\n{LAST_DAY_DUE_DATE} / {AUTO_TEMPORARY_DAY},\nPlease pay as soon as possible.\n- {COMPANY_NAME}",
                'template_ex_bn' => "আপনার NET বিল বকেয়া আছে {AMOUNT}/=,\nID- {CUSTOMER_ID},\nবিলটি পরিশোধ করার জন্য অনুরোধ করা হচ্ছে।\n- {COMPANY_NAME}",
                'is_active' => false,
            ],
            [
                'template_name' => 'area_wise_customer_list',
                'template' => "Dear {CUSTOMER_NAME}, Let us know if any problems you may have with your service and advice us to help you with it. -{COMPANY_NAME},{COMPANY_MOBILE}",
                'template_ex_en' => "Example 1:\n</br>\n{CUSTOMER_NAME},\nLet us know if any problems you may have with your service and\nadvice us to help you with it.\n-{COMPANY_NAME},{COMPANY_MOBILE}\n</br></br>\nExample 2:\n</br>\nYour NET Service {MONTH}'s bill is {BILL_AMOUNT}Tk.\nPlease pay before {LAST_DAY_OF_PAY_BILL}.\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_bn' => "Example 1:\n</br>\nআপনার NET সার্ভিস পেতে যেকোনো সমস্যা আমাদেরকে জানাবেন।\n-{COMPANY_NAME},{COMPANY_MOBILE}\n</br></br>\nExample 2:\n</br>\nআপনার NET এর, {MONTH} মাসের বিল {BILL_AMOUNT}/=,\nবিল দেয়ার শেষ দিন {LAST_DAY_OF_PAY_BILL},\n-{COMPANY_NAME}, {COMPANY_MOBILE}",
                'is_active' => false,
            ],
            [
                'template_name' => 'auto_temporary_disable_alert',
                'template' => "Dear {CUSTOMER_NAME}, ID: {CUSTOMER_ID}, Your connection is temporarily disconnected. Due is {DUE_AMOUNT}/=, - {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_en' => "{CUSTOMER_NAME},\nID: {CUSTOMER_ID},\nYour connection is temporarily disconnected.\nDue is {DUE_AMOUNT}/=,\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_bn' => "{CUSTOMER_NAME},\nID- {CUSTOMER_ID},\nআপনার সংযোগ অস্থায়ীভাবে বন্ধ হয়েছে।\nবকেয়া {DUE_AMOUNT}/=,\n- {COMPANY_NAME}",
                'is_active' => true,
            ],
            [
                'template_name' => 'bill_generate',
                'template' => "Your NET {MONTH}'s bill is {BILL_AMOUNT}/=, ID- {CUSTOMER_ID} Please pay before {LAST_DAY_OF_PAY_BILL}. -{COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_en' => "Your NET {MONTH}'s bill is {BILL_AMOUNT}/=,\nID- {CUSTOMER_ID}\nPlease pay before {LAST_DAY_OF_PAY_BILL}.\n-{COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_bn' => "আপনার NET এর, {MONTH} মাসের বিল {BILL_AMOUNT}/=,\nID- {CUSTOMER_ID}\nবিল দেয়ার শেষ দিন {LAST_DAY_OF_PAY_BILL},\n-{COMPANY_NAME}",
                'is_active' => true,
            ],
            [
                'template_name' => 'payment_collection',
                'template' => "Dear {CUSTOMER_NAME}, We got {AMOUNT}/=, for {IP_OR_USER_NAME_OR_ID}, Your {BALANCE}/=, - {COMPANY_NAME}",
                'template_ex_en' => "Dear {CUSTOMER_NAME}, We got {AMOUNT}/=, for {IP_OR_USER_NAME_OR_ID}, Your {BALANCE}/=, - {COMPANY_NAME}",
                'template_ex_bn' => "ইন্টারনেট বিল পরিশোধ {AMOUNT}/=,\nUsername/IP- {IP_OR_USER_NAME_OR_ID},\nবকেয়া {BALANCE}/=,\n- {COMPANY_NAME}",
                'is_active' => true,
            ],
            [
                'template_name' => 'collection_(MFS)_to_owner',
                'template' => "Got a payment of {AMOUNT}, from {CUSTOMER_NAME}, ID- {CUSTOMER_ID}, at {PAYMENT_SYSTEM}.",
                'template_ex_en' => "Got a payment of {AMOUNT}, from {CUSTOMER_NAME},\nID- {CUSTOMER_ID},\nat {PAYMENT_SYSTEM}.",
                'template_ex_bn' => "টাকা কালেকশন- {AMOUNT}/=,\nID- {CUSTOMER_ID},\n{PAYMENT_SYSTEM} থেকে।",
                'is_active' => false,
            ],
            [
                'template_name' => 'collection_delete',
                'template' => "{CUSTOMER_NAME},\nYour {AMOUNT}Tk.\nthe collection has been canceled.\nYou paid {TOTAL_COLLECTION}Tk.\nFor any questions,\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_en' => "{CUSTOMER_NAME},\nYour {AMOUNT}Tk.\nthe collection has been canceled.\nYou paid {TOTAL_COLLECTION}Tk.\nFor any questions,\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_bn' => "ইন্টারনেট বিলের টাকা ভুল করে {AMOUNT}/= জমা হয়েছিল।\nমোট {TOTAL_COLLECTION}/= দিয়েছেন,\nপ্রশ্ন থাকলে {COMPANY_MOBILE}\nযোগাযোগ করুন।",
                'is_active' => true,
            ],
            [
                'template_name' => 'collection_edit',
                'template' => "{CUSTOMER_NAME},\nYour collection amount {PREVIOUS_COLLECTION_AMOUNT}Tk.\nhas been updated with {CURRENT_COLLECTION_AMOUNT}Tk.\nThanks- {COMPANY_NAME}",
                'template_ex_en' => "{CUSTOMER_NAME},\nYour collection amount {PREVIOUS_COLLECTION_AMOUNT}Tk.\nhas been updated with {CURRENT_COLLECTION_AMOUNT}Tk.\nThanks- {COMPANY_NAME}",
                'template_ex_bn' => "ইন্টারনেট বিল ভুল করে\n{PREVIOUS_COLLECTION_AMOUNT}/= জমা দেয়া হয়েছিল।\nএখন সঠিক ভাবে {CURRENT_COLLECTION_AMOUNT}/=\nজমা দেয়া হয়েছে।\n- {COMPANY_NAME}",
                'is_active' => true,
            ],
            [
                'template_name' => 'collection_to_owner',
                'template' => "Amount Collection {AMOUNT} for {CUSTOMER_NAME},\nID- {CUSTOMER_ID}\nat EasyEBilling Software.",
                'template_ex_en' => "Amount Collection {AMOUNT} for {CUSTOMER_NAME},\nID- {CUSTOMER_ID}\nat EasyEBilling Software.",
                'template_ex_bn' => "টাকা কালেকশন- {AMOUNT}/=,\n{CUSTOMER_NAME},\nID- {CUSTOMER_ID}",
                'is_active' => false,
            ],
            [
                'template_name' => 'complain_employee',
                'template' => "New Complain-\n{CUSTOMER_NAME},\nID: {CUSTOMER_ID},\nIP: {IP},\nPPPoE: {PPPOE_USERNAME},\n{CUSTOMER_MOBILE},\nComplain: {COMPLAINS},\nComment: {COMMENT},\nAddress: {CUSTOMER_ADDRESS}",
                'template_ex_en' => "New Complain-\n{CUSTOMER_NAME},\nID: {CUSTOMER_ID},\nIP: {IP},\nPPPoE: {PPPOE_USERNAME},\n{CUSTOMER_MOBILE},\nComplain: {COMPLAINS},\nComment: {COMMENT},\nAddress: {CUSTOMER_ADDRESS}",
                'template_ex_bn' => "নতুন অভিযোগ-\n{CUSTOMER_NAME},\nIP: {IP},\nPPPoE: {PPPOE_USERNAME},\n{CUSTOMER_MOBILE},\nঅভিযোগ: {COMPLAINS},\nমন্তব্য: {COMMENT},\nঠিকানা: {CUSTOMER_ADDRESS}",
                'is_active' => false,
            ],
            [
                'template_name' => 'complain_list',
                'template' => "Dear\n{CUSTOMER_NAME},\nYour problem has been resolved.\nIf needed give us a call {COMPANY_MOBILE}.\n- {COMPANY_NAME}",
                'template_ex_en' => "Dear\n{CUSTOMER_NAME},\nYour problem has been resolved.\nIf needed give us a call {COMPANY_MOBILE}.\n- {COMPANY_NAME}",
                'template_ex_bn' => "{CUSTOMER_NAME},\nআপনার সমস্যাটি সমাধান করা হয়েছে।\nপ্রয়োজনে ফোন করুন {COMPANY_MOBILE},\n- {COMPANY_NAME}",
                'is_active' => false,
            ],
            [
                'template_name' => 'complain_to_customer',
                'template' => "{CUSTOMER_NAME},\nYour complain is: {COMPLAINS}, {COMMENT},\njust arised. {EMPLOYEE_NAME}, {EMPLOYEE_MOBILE}\nwill contact with you- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_en' => "{CUSTOMER_NAME},\nYour complain is: {COMPLAINS}, {COMMENT},\njust arised. {EMPLOYEE_NAME}, {EMPLOYEE_MOBILE}\nwill contact with you- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_bn' => "আপনার অভিযোগ {COMPLAINS}, {COMMENT} পেয়েছি।\n{EMPLOYEE_NAME}, {EMPLOYEE_MOBILE} \nআপনার সাথে যোগাযোগ করবে।\n-{COMPANY_NAME}",
                'is_active' => false,
            ],
            [
                'template_name' => 'create_customer',
                'template' => "{CUSTOMER_NAME},\nID: {CUSTOMER_ID},\nIP: {IP},\nUsername: {PPPOE_USERNAME},\nEnjoy your new connection.\n-{COMPANY_NAME}",
                'template_ex_en' => "{CUSTOMER_NAME},\nID: {CUSTOMER_ID},\nIP: {IP},\nUsername: {PPPOE_USERNAME},\nEnjoy your new connection.\n-{COMPANY_NAME}",
                'template_ex_bn' => "আমাদের ইন্টারনেট সংযোগ নেওয়ার জন্য ধন্যবাদ\nID: {CUSTOMER_ID}\nIP: {IP}\nUsername: {PPPOE_USERNAME}\nPassword : {PPPOE_PASSWORD}\n- {COMPANY_NAME}",
                'is_active' => false,
            ],
            [
                'template_name' => 'create_customer_to_owner',
                'template' => "A new customer just created-\n{CUSTOMER_NAME},\nID- {CUSTOMER_ID},\nIP- {IP},\nPPPoE Username- {PPPOE_USERNAME}",
                'template_ex_en' => "A new customer just created-\n{CUSTOMER_NAME},\nID- {CUSTOMER_ID},\nIP- {IP},\nPPPoE Username- {PPPOE_USERNAME}",
                'template_ex_bn' => "নতুন গ্রাহক-\n{CUSTOMER_NAME},\nIP- {IP},\nPPPoE ID- {PPPOE_USERNAME}",
                'is_active' => false,
            ],
            [
                'template_name' => 'free_customer_list',
                'template' => "Dear\n{CUSTOMER_NAME},\nYou are given a complimentary free connection.\nIf you have a query let us know.\n- {COMPANY_NAME} {COMPANY_MOBILE}",
                'template_ex_en' => "Dear\n{CUSTOMER_NAME},\nYou are given a complimentary free connection.\nIf you have a query let us know.\n- {COMPANY_NAME} {COMPANY_MOBILE}",
                'template_ex_bn' => "{CUSTOMER_NAME},\nআপনাকে একটি কমপ্লিমেন্টারি, ফ্রী সংযোগ দেওয়া হলো,\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'is_active' => false,
            ],
            [
                'template_name' => 'inactive_customer_list',
                'template' => "{CUSTOMER_NAME},\nYour connection still remains inactive.\nTotal due is {DUE_AMOUNT}/=, to turn ON the connection\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_en' => "{CUSTOMER_NAME},\nYour connection still remains inactive.\nTotal due is {DUE_AMOUNT}/=, to turn ON the connection\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_bn' => "আপনার NET সংযোগটি এখনও বন্ধ।\nসংযোগ ON করতে যোগাযোগ করুন।\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'is_active' => false,
            ],
            [
                'template_name' => 'failed_to_disable_at_mikrotik',
                'template' => "{CUSTOMER_NAME},\nID- {ID},\nIP- {IP},\nPPPoE ID- {PPPOE_USERNAME}\nEnjoy your internet connection.\n- {COMPANY_NAME} {COMPANY_MOBILE}",
                'template_ex_en' => "{CUSTOMER_NAME},\nID- {ID},\nIP- {IP},\nPPPoE ID- {PPPOE_USERNAME}\nEnjoy your internet connection.\n- {COMPANY_NAME} {COMPANY_MOBILE}",
                'template_ex_bn' => "{CUSTOMER_NAME},\nID- {ID},\nIP- {IP},\nPPPoE ID- {PPPOE_USERNAME}\nইন্টারনেটের সমস্যা থাকলে-\n{COMPANY_NAME}, {COMPANY_MOBILE}",
                'is_active' => false,
            ],
            [
                'template_name' => 'temporary_disable_customer_list',
                'template' => "{CUSTOMER_NAME},\nYour NET service is temporarily disconnected. \nThe due amount is {DUE_AMOUNT}/=,\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_en' => "{CUSTOMER_NAME},\nYour NET service is temporarily disconnected. \nThe due amount is {DUE_AMOUNT}/=,\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_bn' => "আপনার NET সংযোগ অস্থায়ীভাবে বন্ধ আছে।\nবকেয়ার পরিমাণ {DUE_AMOUNT}/=,\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'is_active' => true,
            ],
            [
                'template_name' => 'reminder',
                'template' => "{CUSTOMER_NAME},\nYour connection will be discontinued from\n{AUTO_TEMPORARY_DAY}. Please pay your bill.\nID- {ID},\nDue- {DUE_AMOUNT}/=,\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_en' => "{CUSTOMER_NAME},\nYour connection will be discontinued from\n{AUTO_TEMPORARY_DAY}. Please pay your bill.\nID- {ID},\nDue- {DUE_AMOUNT}/=,\n- {COMPANY_NAME}, {COMPANY_MOBILE}",
                'template_ex_bn' => "আপনার সংযোগটি {AUTO_TEMPORARY_DAY} বন্ধ হবে।\nবন্ধ এড়াতে বিল পরিশোধ করুন।\nID- {ID},\nবকেয়া- {DUE_AMOUNT}/=,\n- {COMPANY_NAME}",
                'is_active' => false,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('sms_templates')->updateOrInsert(
                ['template_name' => $template['template_name']],
                [
                    'template' => $template['template'],
                    'template_ex_en' => $template['template_ex_en'],
                    'template_ex_bn' => $template['template_ex_bn'],
                    'is_active' => $template['is_active'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
