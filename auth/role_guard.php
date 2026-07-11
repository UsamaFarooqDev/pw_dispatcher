<?php
// Returns true if the current session belongs to the restricted "dispatcher"
// operator role. Any other/unset role (e.g. 'admin', or sessions created
// before roles existed) keeps full existing access.
function isDispatcherRole() {
    return (($_SESSION['role'] ?? 'admin') === 'dispatcher');
}
