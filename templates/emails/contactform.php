<?php
    
// map field labels to data names
$out = cockpit('formvalidation')->nameToLabel($data, $frm);

// templating in email_text_after and email_text_before
$email_text_before = isset($frm['email_text_before']) && !empty($frm['email_text_before']) ? cockpit('formvalidation')->map($frm['email_text_before'], $data) : false;

$email_text_after = isset($frm['email_text_after']) && !empty($frm['email_text_after']) ? cockpit('formvalidation')->map($frm['email_text_after'], $data) : false;

?>
<!DOCTYPE HTML>
<html><head><meta charset="utf-8" /></head><body>
<?php if ($email_text_before): ?>
<p>{{ $email_text_before }}</p>
<?php endif; ?>
<?php foreach ($out as $field => $val): ?>
<p><strong>{{ $field }}:</strong><br />
{{ $val }}</p>
<?php endforeach;?>
<?php if ($email_text_after): ?>
<p>{{ $email_text_after }}</p>
<?php endif; ?>
</body></html>
