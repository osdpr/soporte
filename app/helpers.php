<?php
// /soporte/app/helpers.php
if (!function_exists('sanitize')) {
  function sanitize($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}
