@if (config('company.bank_name'))
    <div style="margin-top:15px;border:1px solid #dee2e6;border-radius:4px;padding:10px 12px;">
        <div style="font-size:10.5px;font-weight:bold;color:#b80330;text-transform:uppercase;letter-spacing:.03em;margin-bottom:6px;">
            Banking Details
        </div>
        <table style="width:100%;border-collapse:collapse;margin:0;">
            <tr>
                <td style="border:none;padding:2px 0;font-size:9.5px;width:50%;"><strong>Bank Name:</strong> {{ config('company.bank_name') }}</td>
                <td style="border:none;padding:2px 0;font-size:9.5px;">
                    <strong>Branch:</strong> {{ config('company.bank_branch') }}
                    @if (config('company.bank_branch_code')) (Code: {{ config('company.bank_branch_code') }}) @endif
                </td>
            </tr>
            <tr>
                <td style="border:none;padding:2px 0;font-size:9.5px;"><strong>Swift Code:</strong> {{ config('company.bank_swift_code') }}</td>
                <td style="border:none;padding:2px 0;font-size:9.5px;"><strong>Account Name:</strong> {{ config('company.bank_account_name') }}</td>
            </tr>
            <tr>
                <td colspan="2" style="border:none;padding:2px 0;font-size:9.5px;"><strong>Bank Address:</strong> {{ config('company.bank_address') }}</td>
            </tr>
        </table>
        <div style="margin-top:8px;padding-top:6px;border-top:1px dashed #dee2e6;font-size:9.5px;">
            <span style="display:inline-block;margin-right:24px;"><strong>USD Account:</strong> {{ config('company.bank_account_number') }}</span>
            @if (config('company.zig_bank_account_number'))
                <span><strong>ZWG Account:</strong> {{ config('company.zig_bank_account_number') }}</span>
            @endif
        </div>
    </div>
@endif
