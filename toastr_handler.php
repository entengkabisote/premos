<?php
$toastMessage = $_SESSION['toastMessage'] ?? null;
$toastType = $_SESSION['toastType'] ?? null;
unset($_SESSION['toastMessage'], $_SESSION['toastType']);

if ($toastMessage):
?>
<script>
$(document).ready(function() {
  toastr["<?= htmlspecialchars($toastType) ?>"]("<?= htmlspecialchars($toastMessage) ?>");
});
</script>
<?php endif; ?>
