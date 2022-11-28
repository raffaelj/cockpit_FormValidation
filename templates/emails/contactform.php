<?php
/**
 * Default email template of the FormValidion addon for Cockpit CMS v1
 */

$formFields = [];
if (!empty($frm['fields']) && is_array($frm['fields'])) {
    foreach ($frm['fields'] as $field) {
        $formFields[$field['name']] = $field;
    }
}

// templating in email_text_after and email_text_before
$email_text_before = isset($frm['email_text_before']) && !empty($frm['email_text_before'])
    ? cockpit('formvalidation')->map($frm['email_text_before'], $data) : false;
$email_text_after  = isset($frm['email_text_after']) && !empty($frm['email_text_after'])
    ? cockpit('formvalidation')->map($frm['email_text_after'], $data) : false;

?>
<!DOCTYPE HTML>
<html><head><meta charset="utf-8" /></head><body>
@if($email_text_before)<p>{{ $email_text_before }}</p>@endif

@foreach($data as $k => $val)
<p><strong>{{ !empty($formFields[$k]['label']) ? htmlspecialchars($formFields[$k]['label']) : $k }}:</strong><br />
@if(isset($formFields[$k]))
    @if($formFields[$k]['type'] == 'multipleselect' || $formFields[$k]['type'] == 'select')
        @if(is_array($val))
            @foreach($val as $opt)
            {{ $formFields[$k]['options']['options'][$opt] ?? $opt }}<br />
            @endforeach
        @else
        {{ is_string($val) ? ($formFields[$k]['options']['options'][$val] ?? $val) : json_encode($val) }}
        @endif
    @elseif($formFields[$k]['type'] == 'file')
        @if(is_array($val))
            @foreach($val as $url)
                @if(is_string($url))
                    {{ $url }}<br />
                @elseif(is_array($url))
                    @foreach($url as $v)
                        {{ is_string($v) ? $v : json_encode($v) }}<br />
                    @endforeach
                @endif
            @endforeach
        @else
            {{ $val }}
        @endif
    @else
        {{ is_string($val) ? $val : json_encode($val) }}
    @endif
@else
{{ is_string($val) ? $val : json_encode($val) }}
@endif
</p>
@endforeach

@if($email_text_after)<p>{{ $email_text_after }}</p>@endif
</body></html>
