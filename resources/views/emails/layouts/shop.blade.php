※このメールはシステムからの自動送信です


@yield('content')

---------------------------------------------------------------------
{{ config('company.company_name') }}
〒{{ config('company.postal_code') }} {{ config('company.address') }}
（{{ config('company.facility_name_in') }}）
TEL: {{ config('company.phone') }} FAX: {{ config('company.fax') }}
Email: {{ config('company.email') }}
URL: {{ config('company.site_url') }}
---------------------------------------------------------------------
