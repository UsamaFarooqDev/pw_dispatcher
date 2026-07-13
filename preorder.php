<?php
session_start();

require_once 'auth/require_login_redirect.php';
require_once 'auth/role_guard.php';
$isDispatcher = isDispatcherRole();
require('modules/head.php');
?>
<!DOCTYPE html>
<html lang="en">
  <body>

   <?php require_once 'modules/navbar.php'; ?>

    <?php @require('modules/sidebar.php'); ?>

    <main class="main-content p-4" style="background:#F4F4F5; min-height:100vh;">

  <?php @require('modules/bodyHeader.php'); ?>

  <!-- Tab header strip -->
  <nav class="po-tabs mt-4" role="tablist" aria-label="Live orders sections">
        <button type="button" class="po-tab is-active tab-btn active-tab" id="tab-unassigned" role="tab" aria-selected="true">
          <i class="bi bi-hourglass-split po-tab__icon"></i>
          <span class="po-tab__label">Unassigned</span>
          <span class="po-tab__count" id="count-unassigned">0</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-assigned" role="tab" aria-selected="false">
          <i class="bi bi-person-check po-tab__icon"></i>
          <span class="po-tab__label">Assigned</span>
          <span class="po-tab__count" id="count-assigned">0</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-enroute" role="tab" aria-selected="false">
          <i class="bi bi-geo-alt po-tab__icon"></i>
          <span class="po-tab__label">Enroute</span>
          <span class="po-tab__count" id="count-enroute">0</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-on-trip" role="tab" aria-selected="false" style="display:none;">
          <i class="bi bi-car-front po-tab__icon"></i>
          <span class="po-tab__label">On Trip</span>
          <span class="po-tab__count" id="count-on-trip">0</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-scheduled" role="tab" aria-selected="false">
          <i class="bi bi-calendar-check po-tab__icon"></i>
          <span class="po-tab__label">Pre-Order</span>
          <span class="po-tab__count" id="count-scheduled">0</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-cancelled" role="tab" aria-selected="false">
          <i class="bi bi-x-circle po-tab__icon"></i>
          <span class="po-tab__label">Cancelled</span>
          <span class="po-tab__count" id="count-cancelled">0</span>
        </button>

        <button type="button" class="po-tab tab-btn" id="tab-finished" role="tab" aria-selected="false">
          <i class="bi bi-check-circle po-tab__icon"></i>
          <span class="po-tab__label">Completed</span>
          <span class="po-tab__count" id="count-finished">0</span>
        </button>

        <?php if (!$isDispatcher): ?>
        <button type="button" class="po-tab tab-btn" id="tab-meet-greet" role="tab" aria-selected="false">
          <i class="bi bi-airplane po-tab__icon"></i>
          <span class="po-tab__label">Meet &amp; Greet</span>
          <span class="po-tab__count" id="count-meet-greet">0</span>
        </button>
        <?php endif; ?>
  </nav>

  <!-- Table content card -->
  <div class="rounded-bottom-3 border border-top-0 overflow-hidden" style="background:#fff; border-color:#EBEBEB !important; box-shadow:0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);">
    <div class="p-4 pt-3">

      <!-- Unassigned -->
      <div id="pane-unassigned" class="tab-pane-table">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Prebook</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Source</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;" id="actionHeader">Action</th>
            </tr></thead>
            <tbody id="unassignedRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Assigned -->
      <div id="pane-assigned" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2" style="border:1px solid #EBEBEB; min-height:362px; overflow-x:auto; overflow-y:hidden;">
          <table class="table mb-0" style="border-collapse:collapse; min-width:1100px;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:120px;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:110px;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:200px;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:200px;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Prebook</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Source</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2 text-end" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:300px;">Action</th>
            </tr></thead>
            <tbody id="assignedRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Enroute -->
      <div id="pane-enroute" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Source</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Driver</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-3 py-2 text-end" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:130px;">Action</th>
            </tr></thead>
            <tbody id="enrouteRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- On Trip -->
      <div id="pane-ontrip" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Prebook</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Source</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Driver</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-3 py-2 text-end" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:130px;">Action</th>
            </tr></thead>
            <tbody id="onTripRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Scheduled -->
      <div id="pane-scheduled" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Category</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">AI Suggestion</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Action</th>
            </tr></thead>
            <tbody id="scheduledRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Cancelled -->
      <div id="pane-cancelled" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
            </tr></thead>
            <tbody id="cancelledRidesBody"></tbody>
          </table>
        </div>
      </div>

      <!-- Completed -->
      <div id="pane-completed" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2" style="border:1px solid #EBEBEB; min-height:362px; overflow-x:auto; overflow-y:hidden;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Name</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Prebook</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Source</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Final Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Toll Amount</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Total Charged</th>
            </tr></thead>
            <tbody id="completedRidesBody"></tbody>
          </table>
        </div>
      </div>

      <?php if (!$isDispatcher): ?>
      <!-- Meet & Greet -->
      <div id="pane-meetgreet" class="tab-pane-table" style="display:none;">
        <div class="table-responsive rounded-2 overflow-hidden" style="border:1px solid #EBEBEB; min-height:362px;">
          <table class="table mb-0" style="border-collapse:collapse;">
            <thead><tr style="background:#FAFAFA; border-bottom:1px solid #EBEBEB;">
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Company</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Employee</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Order Time</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Pickup</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Destination</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Payment</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Fare</th>
              <th class="fw-semibold text-nowrap px-4 py-2" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none;">Status</th>
              <th class="fw-semibold text-nowrap px-3 py-2 text-end" style="font-size:0.775rem; color:#71717A; letter-spacing:0.04em; text-transform:uppercase; border:none; min-width:130px;">Action</th>
            </tr></thead>
            <tbody id="meetGreetRidesBody"></tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <div id="preorderPaginationContainer" class="mt-3"></div>

    </div>
  </div>

</main>

<style>
  /* ── Live Orders — bold header tab bar ──────────────────────────────── */
  .po-tabs {
    display: flex;
    align-items: center;
    gap: 0;
    padding: 0 4px;
    background: #E4E4E7;
    border-radius: 10px 10px 0 0;
    border: 1px solid #D4D4D8;
    border-bottom: none;
    overflow-x: auto;
    scrollbar-width: none;
  }
  .po-tabs::-webkit-scrollbar { display: none; }
  .po-tab {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 12px 18px;
    background: none;
    border: none;
    border-bottom: 2.5px solid transparent;
    color: #71717A;
    font-family: inherit;
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    line-height: 1;
    white-space: nowrap;
    cursor: pointer;
    transition: color 0.15s, border-color 0.15s, background 0.15s;
  }
  .po-tab:hover { color: #18181B; }
  .po-tab:focus-visible { outline: none; color: #18181B; }
  .po-tab__icon {
    font-size: 13px;
    color: #A1A1AA;
    line-height: 1;
    transition: color 0.15s;
  }
  .po-tab__label { white-space: nowrap; }
  .po-tab__count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 17px;
    padding: 0 5px;
    background: #E4E4E7;
    color: #71717A;
    font-size: 0.65rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    line-height: 1;
    border-radius: 9px;
    transition: background 0.15s, color 0.15s;
  }
  .po-tab.is-active,
  .po-tab.active-tab {
    color: #f37a20;
    border-bottom-color: #f37a20;
    background: rgba(255,255,255,0.6);
  }
  .po-tab.is-active .po-tab__icon,
  .po-tab.active-tab .po-tab__icon { color: #f37a20; }
  .po-tab.is-active .po-tab__count,
  .po-tab.active-tab .po-tab__count {
    background: #FFF3E8;
    color: #f37a20;
  }
  @media (max-width: 767.98px) {
    .po-tab { padding: 10px 12px; font-size: 0.7rem; }
  }
  .tab-btn.active-tab { color: #f37a20; }
  #unassignedRidesBody tr, #assignedRidesBody tr, #enrouteRidesBody tr,
  #scheduledRidesBody tr, #cancelledRidesBody tr, #completedRidesBody tr,
  #meetGreetRidesBody tr {
    border-bottom: 1px solid #F4F4F5;
    transition: background 0.12s;
  }
  #unassignedRidesBody tr:hover, #assignedRidesBody tr:hover, #enrouteRidesBody tr:hover,
  #scheduledRidesBody tr:hover, #cancelledRidesBody tr:hover,
  #completedRidesBody tr:hover, #meetGreetRidesBody tr:hover { background: #FAFAFA; }
  #unassignedRidesBody td, #assignedRidesBody td, #enrouteRidesBody td,
  #scheduledRidesBody td, #cancelledRidesBody td, #completedRidesBody td,
  #meetGreetRidesBody td {
    padding: 14px 16px;
    font-size: 0.845rem;
    color: #18181B;
    vertical-align: middle;
    border: none;
  }
  /* Keep the M&G action cell from being squeezed into invisibility */
  #meetGreetRidesBody td:last-child { white-space: nowrap; min-width: 130px; }
  /* ── AI dispatch-suggestion button (Pre-Order tab, unassigned rows) ── */
  .ai-suggest-btn {
    display: inline-flex; align-items: center; gap: 5px;
    border: 1px solid #DDD6FE; background: #F5F3FF; color: #7C3AED;
    border-radius: 999px; padding: 4px 11px; font-size: 0.75rem; font-weight: 600;
    cursor: pointer; transition: background 0.12s, border-color 0.12s;
  }
  .ai-suggest-btn:hover { background: #EDE9FE; border-color: #C4B5FD; }
  .ai-suggest-btn:disabled { opacity: 0.6; cursor: default; }
  .ai-suggest-btn.is-playing { background: #7C3AED; color: #fff; border-color: #7C3AED; }
  /* Anchor for the floating popover */
  .ai-suggest-wrap { position: relative; display: inline-block; }
  /* Floating popover — overlays instead of expanding the table row */
  .ai-suggest-text {
    position: absolute; top: calc(100% + 6px); right: 0; z-index: 1080;
    width: 260px; white-space: normal; text-align: left;
    font-size: 0.78rem; line-height: 1.35; color: #4C1D95;
    background: #FAF5FF; border: 1px solid #EDE9FE; border-radius: 8px; padding: 8px 10px;
    box-shadow: 0 8px 24px rgba(76, 29, 149, 0.18);
  }
  /* Little caret pointing back at the button */
  .ai-suggest-text::before {
    content: ''; position: absolute; top: -5px; right: 16px;
    width: 9px; height: 9px; background: #FAF5FF;
    border-left: 1px solid #EDE9FE; border-top: 1px solid #EDE9FE;
    transform: rotate(45deg);
  }
  /* Let the popover escape the Pre-Order table's clipped container */
  #pane-scheduled .table-responsive { overflow: visible; }
  .ai-suggest-text .ai-replay {
    margin-left: 6px; color: #7C3AED; cursor: pointer; border: none; background: none; padding: 0;
  }
  .ai-suggest-text.is-error { color: #B91C1C; background: #FEF2F2; border-color: #FEE2E2; }
</style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/pagination.js"></script>
    <script>

      // Dispatcher role: only ever see Powercabs Dispatch-created orders —
      // app/corporate rides and the Meet & Greet tab are filtered out entirely.
      const IS_DISPATCHER_ROLE = <?php echo json_encode($isDispatcher); ?>;
      function isDispatcherSourcedRide(ride) {
        if (!IS_DISPATCHER_ROLE) return true;
        return String(ride?.source || '').toLowerCase().includes('dispatch');
      }

      // Configuration: Polling interval in milliseconds (default: 10 seconds)
      const POLLING_INTERVAL_MS = 10 * 1000; // 10 seconds

      let pollingInterval = null;
      let beepingRideIds = new Set(); // Track which rides are currently beeping
       let previousRideStatuses = new Map(); // Track previous status of each ride to detect transitions
      let currentRidesData = {
        unassigned: [],
        assigned: [],
        ontrip: [],
        scheduled: [],
        cancelled: [],
        completed: [],
        meetgreet: []
      }; // Cache current rides data to detect changes
      
      // Pagination
      let preorderPagination = null;
      const ITEMS_PER_PAGE = 10;
      let currentTab = 'unassigned'; // Track current active tab

      document.addEventListener('DOMContentLoaded', function () {
        // Setup tab switching first — must always run regardless of other errors
        setupTabSwitching();
        setupPreorderSearch();

        // Initialize pagination
        try {
          const paginationContainer = document.getElementById('preorderPaginationContainer');
          if (paginationContainer && typeof PaginationManager !== 'undefined') {
            preorderPagination = new PaginationManager({
              containerId: 'preorderPaginationContainer',
              page: 1,
              limit: ITEMS_PER_PAGE,
              total: 0,
              onPageChange: (page, limit) => {
                updateTableForCurrentTab(page, limit);
              }
            });
          }
        } catch (e) {
          console.warn('Pagination init error:', e);
        }

        // Sync beeping state
        if (window.BeepMonitor) {
          beepingRideIds = new Set(window.BeepMonitor.getBeepingRideIds());
        }

        // Load all tab data
        loadUnassignedRides(true);
        loadAssignedRides(true);
        loadEnrouteRides(true);
        loadOnTripRides(true);
        loadScheduledRides(true);
        loadCancelledRides(true);
        loadCompletedRides(true);
        if (!IS_DISPATCHER_ROLE) loadMeetGreetRides(true);

        // Start polling
        startPolling();
      });

      // Cleanup on page unload
      window.addEventListener('beforeunload', function () {
        stopPolling();
      });

      function startPolling() {
        if (pollingInterval) {
          clearInterval(pollingInterval);
        }
        pollingInterval = setInterval(() => {
          // Silent status check - only update UI if there are changes
          checkRideStatusChanges();
        }, POLLING_INTERVAL_MS);
      }

      function stopPolling() {
        if (pollingInterval) {
          clearInterval(pollingInterval);
          pollingInterval = null;
        }
      }

      function toggleActionColumn(show) {
  // Toggle header
  const actionHeader = document.getElementById('actionHeader');
  if (actionHeader) {
    actionHeader.style.display = show ? '' : 'none';
  }

  // Toggle action cells (last column in each row)
  document.querySelectorAll('tbody tr').forEach(row => {
    const actionCell = row.lastElementChild;
    if (actionCell) {
      actionCell.style.display = show ? '' : 'none';
    }
  });
}

      // Silent status check - fetches data and only updates UI if there are changes
      async function checkRideStatusChanges() {
        try {
          // Fetch all rides (use high limit to get all rides for client-side filtering)
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }

          const result = await response.json();
          
          // Check if API returned success
          if (!result.success) {
            throw new Error(result.error || 'Failed to fetch rides');
          }
          
          const rides = (result && result.data ? result.data : []).filter(isDispatcherSourcedRide);

          const unassignedRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'searching';
          });

          const assignedRides = rides.filter(isAssignedTabRide);

          // All scheduled-status rides — used for the 40-min auto-activation below.
          const allScheduledRides = rides.filter(isScheduledStatus);
          // Scheduled TAB shows only those without a driver; driver-assigned
          // scheduled rides are surfaced in the Assigned tab instead.
          const scheduledRides = allScheduledRides.filter((ride) => !rideHasDriver(ride));

          const enrouteRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return ENROUTE_STATUSES.includes(status);
          });

          const onTripRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return ['on_trip','ongoing','in_progress','ontrip','started','arrived_at_pickup','driver_arrived','arrived'].includes(status);
          });

          const cancelledRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'cancelled' || status === 'canceled';
          });

          const completedRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'completed' || status === 'finished';
          });

             // Initialize previous statuses on first check if empty
          if (previousRideStatuses.size === 0) {
            rides.forEach((ride) => {
              if (ride.id) {
                previousRideStatuses.set(ride.id, (ride.status || '').toLowerCase());
              }
            });
          }

          // Auto-transition scheduled rides based on time proximity (include
          // driver-assigned scheduled rides so they still activate at the 40-min mark).
          await processScheduledRideTransitions(allScheduledRides);

          // Check for beeping rides (always check, regardless of UI updates)
          checkForBeepingRides(unassignedRides, rides);

          // Only update UI if there are actual changes
          const unassignedChanged = hasRidesChanged(currentRidesData.unassigned, unassignedRides);
          const assignedChanged = hasRidesChanged(currentRidesData.assigned, assignedRides);
          const enrouteChanged = hasRidesChanged(currentRidesData.enroute, enrouteRides);
          const scheduledChanged = hasRidesChanged(currentRidesData.scheduled, scheduledRides);

          if (unassignedChanged) {
            currentRidesData.unassigned = unassignedRides;
            updateUnassignedTabCount(unassignedRides.length);
            if (currentTab === 'unassigned') {
              updatePaginationInfo(unassignedRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }

          if (assignedChanged) {
            currentRidesData.assigned = assignedRides;
            updateAssignedTabCount(assignedRides.length);
            if (currentTab === 'assigned') {
              updatePaginationInfo(assignedRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }

          if (enrouteChanged) {
            currentRidesData.enroute = enrouteRides;
            updateEnrouteTabCount(enrouteRides.length);
            if (currentTab === 'enroute') {
              updatePaginationInfo(enrouteRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }

          if (scheduledChanged) {
            currentRidesData.scheduled = scheduledRides;
            updateScheduledTabCount(scheduledRides.length);
            if (currentTab === 'scheduled') {
              updatePaginationInfo(scheduledRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }

          const cancelledChanged = hasRidesChanged(currentRidesData.cancelled, cancelledRides);
          if (cancelledChanged) {
            currentRidesData.cancelled = cancelledRides;
            updateCancelledTabCount(cancelledRides.length);
            if (currentTab === 'cancelled') {
              updatePaginationInfo(cancelledRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }

          const completedChanged = hasRidesChanged(currentRidesData.completed, completedRides);
          if (completedChanged) {
            currentRidesData.completed = completedRides;
            updateCompletedTabCount(completedRides.length);
            if (currentTab === 'completed') {
              updatePaginationInfo(completedRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }

          const onTripChanged = hasRidesChanged(currentRidesData.ontrip, onTripRides);
          if (onTripChanged) {
            currentRidesData.ontrip = onTripRides;
            updateOnTripTabCount(onTripRides.length);
            if (currentTab === 'ontrip') {
              updatePaginationInfo(onTripRides.length);
              const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
              updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
            }
          }
        } catch (error) {
          console.error('Error checking ride status:', error);
          // Don't show error in UI during silent polling - only log it
        }
      }

      // Auto-transition scheduled rides when pickup time is ≤ 40 min away.
      //
      // Two paths:
      //   1. Pre-assigned driver present
      //        → call assign_driver.php with force_assign=true
      //          This writes driver_id + status='assigned' + updated_at in one shot —
      //          exactly the same field set written by an immediate dispatch.
      //          Supabase Real-time fires the same UPDATE event shape the driver app
      //          already subscribes to, so the trip request popup appears as normal.
      //
      //   2. No driver assigned yet
      //        → call update_ride_status.php to set status='searching'
      //          Ride is broadcast to all available drivers the usual way.
      //
      // Rides are NEVER auto-cancelled here; only a human action may cancel.
      const _transitioningRideIds = new Set();
      async function processScheduledRideTransitions(scheduledRides) {
        const now = Date.now();
        for (const ride of scheduledRides) {
          if (!ride.id) continue;
          if (_transitioningRideIds.has(ride.id)) continue;

          // Resolve scheduled time: prefer the dedicated column, fall back to meta JSON.
          let scheduledAt = ride.scheduled_at || null;
          if (!scheduledAt && ride.meta) {
            try {
              const meta = typeof ride.meta === 'string' ? JSON.parse(ride.meta) : ride.meta;
              scheduledAt = meta.scheduled_datetime || null;
            } catch (_) {}
          }
          if (!scheduledAt) continue;

          const scheduledMs = new Date(scheduledAt).getTime();
          if (isNaN(scheduledMs)) continue;

          const diffMin = (scheduledMs - now) / 60000;
          if (diffMin > 40) continue; // not yet in the activation window

          _transitioningRideIds.add(ride.id);
          try {
            if (ride.driver_id) {
              // Path 1 — pre-assigned driver: use the same assign_driver endpoint
              // so the driver app receives the identical Real-time event as an
              // instant assigned ride.
              await fetch('api/assign_driver.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                  ride_id:      ride.id,
                  driver_id:    ride.driver_id,
                  force_assign: true   // bypass scheduled-status preservation
                })
              });
            } else {
              // Path 2 — no driver: open for broadcast searching.
              await fetch('api/update_ride_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ride_id: ride.id, status: 'searching' })
              });
            }
          } catch (e) {
            console.error('Scheduled transition error for ride', ride.id, e);
          } finally {
            _transitioningRideIds.delete(ride.id);
          }
        }
      }

      // Compare two arrays of rides to detect changes
      function hasRidesChanged(oldRides, newRides) {
        if (oldRides.length !== newRides.length) {
          return true;
        }

        // Create maps of ride IDs and their statuses
        const oldMap = new Map();
        oldRides.forEach(ride => {
          if (ride.id) {
            oldMap.set(ride.id, {
              status: (ride.status || '').toLowerCase(),
              updated_at: ride.updated_at
            });
          }
        });

        const newMap = new Map();
        newRides.forEach(ride => {
          if (ride.id) {
            newMap.set(ride.id, {
              status: (ride.status || '').toLowerCase(),
              updated_at: ride.updated_at
            });
          }
        });

        // Check if any ride IDs are different
        if (oldMap.size !== newMap.size) {
          return true;
        }

        // Check if any ride status or updated_at has changed
        for (const [id, newData] of newMap) {
          const oldData = oldMap.get(id);
          if (!oldData || oldData.status !== newData.status || oldData.updated_at !== newData.updated_at) {
            return true;
          }
        }

        // Check if any old ride is missing in new rides
        for (const id of oldMap.keys()) {
          if (!newMap.has(id)) {
            return true;
          }
        }

        return false;
      }

      async function loadUnassignedRides(showLoading = false) {
        const tbody = document.getElementById('unassignedRidesBody');

        if (showLoading && tbody) {
          tbody.innerHTML =
            '<tr><td colspan="10" class="text-center py-4 text-muted">Loading rides...</td></tr>';
        }

        try {
          // Fetch all rides (use high limit to get all rides for client-side filtering)
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }

          const result = await response.json();
          
          // Check if API returned success
          if (!result.success) {
            throw new Error(result.error || 'Failed to fetch rides');
          }
          
          const rides = (result && result.data ? result.data : []).filter(isDispatcherSourcedRide);

          const unassignedRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'searching';
          });

             // Initialize previous statuses on first load if empty
          if (previousRideStatuses.size === 0) {
            rides.forEach((ride) => {
              if (ride.id) {
                previousRideStatuses.set(ride.id, (ride.status || '').toLowerCase());
              }
            });
          }

          // Check for rides that need beeping
          checkForBeepingRides(unassignedRides, rides);

          // Update cache
          currentRidesData.unassigned = unassignedRides;

          updateUnassignedTabCount(unassignedRides.length);
          
          // Always update table for unassigned tab (default tab)
          if (currentTab === 'unassigned' || currentTab === null || currentTab === undefined) {
            currentTab = 'unassigned';
            try {
              if (preorderPagination && typeof preorderPagination.getCurrentPage === 'function') {
                updatePaginationInfo(unassignedRides.length);
                const currentPage = preorderPagination.getCurrentPage();
                updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
              } else {
                // If pagination not ready yet, just populate all rides
                populateUnassignedTable(unassignedRides);
              }
            } catch (paginationError) {
              console.error('Error updating pagination/table:', paginationError);
              // Fallback: just populate the table
              populateUnassignedTable(unassignedRides);
            }
          }
        } catch (error) {
          console.error('Error loading unassigned rides:', error);
          console.error('Error details:', error.message, error.stack);
          if (tbody) {
            tbody.innerHTML =
              '<tr><td colspan="10" class="text-center py-4 text-danger">Error loading rides. Please refresh the page.</td></tr>';
          }
          if (preorderPagination) {
            preorderPagination.update(0, 1);
          }
          updateUnassignedTabCount(0);
        } finally {
          if (typeof hideGlobalLoader === 'function') hideGlobalLoader();
        }
      }

      function checkForBeepingRides(unassignedRides, allRides) {
        const currentBeepingIds = new Set();
            const newSearchingRides = []; // Rides that just entered searching status

          // Track current status of all rides
        const currentRideStatuses = new Map();
        allRides.forEach((ride) => {
          if (ride.id) {
              const status = (ride.status || '').toLowerCase();
            currentRideStatuses.set(ride.id, status);
          }
        });

        // Check for rides that just transitioned to 'searching' status
        currentRideStatuses.forEach((status, rideId) => {
          if (status === 'searching') {
            currentBeepingIds.add(rideId);

            // Check if this ride just entered searching status
            const previousStatus = previousRideStatuses.get(rideId);
            if (previousStatus !== 'searching') {
              // This ride just entered searching status - record timestamp
              // If ride has updated_at, use that; otherwise use current time
              const ride = allRides.find(r => r.id === rideId);
              const timestamp = ride && ride.updated_at 
                ? new Date(ride.updated_at).getTime() 
                : Date.now();

              newSearchingRides.push({
                rideId: rideId,
                timestamp: timestamp
              });
            }
                }
        });

        // Update previous statuses for next check
        previousRideStatuses = currentRideStatuses;

        // Remove rides that are no longer searching from beeping set
        beepingRideIds.forEach((rideId) => {
          if (!currentBeepingIds.has(rideId)) {
            // Ride is no longer searching, remove from beeping set
          }
        });

        // Update beeping set
        beepingRideIds = currentBeepingIds;

        // Control beep based on whether we should be beeping
        // Use global BeepMonitor to ensure beep works across all pages
        if (window.BeepMonitor) {
          if (beepingRideIds.size > 0) {
                // Pass ride IDs and timestamps for newly searching rides
            window.BeepMonitor.setBeepingRideIds(Array.from(beepingRideIds), newSearchingRides);
          } else {
             window.BeepMonitor.setBeepingRideIds([], []);
            window.BeepMonitor.stopBeep();
          }
        }
      }

      function populateUnassignedTable(rides) {
        try {
          const tbody = document.getElementById('unassignedRidesBody');
          if (!tbody) {
            console.error('unassignedRidesBody element not found');
            return;
          }

          tbody.innerHTML = '';

          if (!rides || rides.length === 0) {
            tbody.innerHTML =
              '<tr><td colspan="10" class="text-center py-4 text-muted">No unassigned rides to show</td></tr>';
            return;
          }

          rides.forEach((ride) => {
            try {
              const name = ride.passenger_name || 'N/A';
              const orderTime = getRideDisplayTime(ride);
              const pickup =
                ride.pickup_addr || ride.actual_start_addr || 'N/A';
              const destination =
                ride.dest_addr || ride.actual_end_addr || 'N/A';
              const status = ride.status || 'N/A';
              const fare = formatFare(ride.fare_eur, ride.estimate_fare);
              const rideId = ride.id || '';

              const row = document.createElement('tr');
              row.innerHTML = `
                <td class="ps-3">${name}</td>
                <td>${orderTime}</td>
                <td>${pickup}</td>
                <td>${destination}</td>
                <td>${renderStatusBadge(status)}</td>
                <td>${renderPaymentBadge(ride.payment_method)}</td>
                <td>${renderPrebookBadge(ride)}</td>
                <td>${renderSourceBadge(ride.source)}</td>
                <td class="text-end pe-4">${fare}</td>
                <td class="text-end pe-4">
                  <a href="orderassigned.php?id=${rideId}&from=unassigned" class="view-details-btn">
                    <span>View Details</span>
                    <i class="bi bi-chevron-right"></i>
                  </a>
                </td>
              `;
              tbody.appendChild(row);
            } catch (rowError) {
              console.error('Error creating table row:', rowError, ride);
            }
          });

          applyPreorderSearchFilterForCurrentTab();
        } catch (error) {
          console.error('Error in populateUnassignedTable:', error);
          const tbody = document.getElementById('unassignedRidesBody');
          if (tbody) {
            tbody.innerHTML =
              '<tr><td colspan="10" class="text-center py-4 text-danger">Error displaying rides data</td></tr>';
          }
        }
      }


           function formatOrderTime(createdAt) {
        if (!createdAt) return 'N/A';
        const date = new Date(createdAt);
        if (Number.isNaN(date.getTime())) return createdAt;

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();

        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${day}.${month}.${year} | ${hours}:${minutes}`;
      }

      function formatScheduledTime(scheduledAt, meta) {
        let ts = scheduledAt || null;
        if (!ts && meta) {
          try {
            const m = typeof meta === 'string' ? JSON.parse(meta) : meta;
            ts = m.scheduled_datetime || null;
          } catch (_) {}
        }
        if (!ts) return '<span style="color:#A1A1AA;">Not set</span>';
        const d = new Date(ts);
        if (isNaN(d.getTime())) return ts;
        const day = String(d.getDate()).padStart(2, '0');
        const mon = d.toLocaleString('en', { month: 'short' });
        const year = d.getFullYear();
        const hours = String(d.getHours()).padStart(2, '0');
        const mins = String(d.getMinutes()).padStart(2, '0');
        return `<div style="line-height:1.3;"><div style="font-weight:600; color:#18181B;">${day} ${mon} ${year}</div><div style="color:#f37a20; font-weight:600; font-size:0.78rem;">${hours}:${mins}</div></div>`;
      }

      function getRideDisplayTime(ride) {
        return formatOrderTime(ride.scheduled_at || ride.created_at);
      }

      function formatFare(fareEur, estimateFare) {
        const value =
          fareEur != null
            ? parseFloat(fareEur)
            : estimateFare != null
            ? parseFloat(estimateFare)
            : null;

        if (value == null || Number.isNaN(value)) return 'N/A';
        return `€${value.toFixed(2)}`;
      }

      function updateUnassignedTabCount(count) {
        const badge = document.getElementById('count-unassigned');
        if (badge) badge.textContent = count;
      }

      function updatePaginationInfo(totalItems) {
        if (preorderPagination) {
          const currentPage = preorderPagination.getCurrentPage();
          preorderPagination.update(totalItems, currentPage);
        }
      }
      
      function updateTableForCurrentTab(page = 1, limit = ITEMS_PER_PAGE) {
        try {
          const rides = currentRidesData[currentTab] || [];
          const start = (page - 1) * limit;
          const end = start + limit;
          const paginatedRides = rides.slice(start, end);

          if (currentTab === 'unassigned') {
            populateUnassignedTable(paginatedRides);
          } else if (currentTab === 'assigned') {
            populateAssignedTable(paginatedRides);
          } else if (currentTab === 'enroute') {
            populateEnrouteTable(paginatedRides);
          } else if (currentTab === 'ontrip') {
            populateOnTripTable(paginatedRides);
          } else if (currentTab === 'scheduled') {
            populateScheduledTable(paginatedRides);
          } else if (currentTab === 'cancelled') {
            populateCancelledTable(paginatedRides);
          } else if (currentTab === 'completed') {
            populateCompletedTable(paginatedRides);
          } else if (currentTab === 'meetgreet') {
            populateMeetGreetTable(paginatedRides);
          }
        } catch (error) {
          console.error('Error in updateTableForCurrentTab:', error);
          const rides = currentRidesData[currentTab] || [];
          if (currentTab === 'unassigned') {
            populateUnassignedTable(rides);
          } else if (currentTab === 'assigned') {
            populateAssignedTable(rides);
          } else if (currentTab === 'ontrip') {
            populateOnTripTable(rides);
          } else if (currentTab === 'scheduled') {
            populateScheduledTable(rides);
          } else if (currentTab === 'cancelled') {
            populateCancelledTable(rides);
          } else if (currentTab === 'completed') {
            populateCompletedTable(rides);
          } else if (currentTab === 'meetgreet') {
            populateMeetGreetTable(rides);
          }
        }
      }

      // ── Assigned vs Scheduled classification ──────────────────────────────
      // Statuses considered "scheduled / pre-order".
      const SCHEDULED_STATUSES = ['upcoming', 'scheduled', 'pending', 'awaiting_assignment'];

      function rideHasDriver(ride) {
        return !!(ride && ride.driver_id != null && String(ride.driver_id).trim() !== '');
      }
      function isScheduledStatus(ride) {
        return SCHEDULED_STATUSES.includes((ride.status || '').toLowerCase());
      }
      // Assigned tab: rides with status 'assigned', plus scheduled rides that
      // already have a driver attached (so the dispatcher can track acceptance).
      function isAssignedTabRide(ride) {
        return (ride.status || '').toLowerCase() === 'assigned'
          || (isScheduledStatus(ride) && rideHasDriver(ride));
      }
      // Scheduled tab: scheduled rides that don't yet have a driver (once a driver
      // is assigned they move to the Assigned tab above).
      function isScheduledTabRide(ride) {
        return isScheduledStatus(ride) && !rideHasDriver(ride);
      }

      // driver_accepted lives in the ride's jsonb `meta` (string or object).
      function getRideMeta(ride) {
        let meta = ride && ride.meta;
        if (typeof meta === 'string') { try { meta = JSON.parse(meta); } catch (_) { meta = null; } }
        return (meta && typeof meta === 'object') ? meta : null;
      }
      function isDriverAccepted(ride) {
        const meta = getRideMeta(ride);
        return !!(meta && meta.driver_accepted === true);
      }

      // A ride is "scheduled" (pre-order / future pickup) when it carries a
      // scheduled_at timestamp or the is_scheduled flag — independent of its
      // current status (a scheduled ride flips to 'assigned' once a driver accepts).
      function isScheduledRide(ride) {
        if (ride && (ride.is_scheduled === true || ride.is_scheduled === 'true')) return true;
        const sa = ride && ride.scheduled_at;
        return sa != null && String(sa).trim() !== '';
      }

      // Status badge for the Assigned tab. A scheduled ride shows whether the
      // driver has accepted (meta.driver_accepted); any other ride shows its real DB status.
      function renderAssignedStatusBadge(ride) {
        const s = (ride.status || '').toLowerCase();
        const driverActed = ['driver_accepted','accepted','arrived_at_pickup','driver_arrived','arrived','on_trip','started','in_progress','trip_started'].includes(s);
        if (isScheduledRide(ride)) {
          return (driverActed || isDriverAccepted(ride))
            ? '<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:#F0FDF4; color:#16A34A; white-space:nowrap;">Accepted</span>'
            : '<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:#FEF3C7; color:#D97706; white-space:nowrap;">Awaiting Acceptance</span>';
        }
        return renderStatusBadge(ride.status || 'N/A');
      }

      // Prebook (pre-booked / scheduled) Yes-No badge.
      function renderPrebookBadge(ride) {
        return isScheduledRide(ride)
          ? '<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:#EFF6FF; color:#2563EB; white-space:nowrap;">Yes</span>'
          : '<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:#F4F4F5; color:#71717A; white-space:nowrap;">No</span>';
      }

      // Booking source badge (e.g. App, Dispatcher, Corporate) from rides.source.
      function renderSourceBadge(rawSource) {
        if (rawSource === null || rawSource === undefined || String(rawSource).trim() === '') {
          return '<span style="color:#A1A1AA; font-size:0.72rem;">—</span>';
        }
        const SRC = {
          app:        { bg: '#EEF2FF', color: '#4F46E5', label: 'App'        },
          dispatcher: { bg: '#FFF3E8', color: '#f37a20', label: 'Dispatcher' },
          corporate:  { bg: '#F5F3FF', color: '#7C3AED', label: 'Corporate'  },
          web:        { bg: '#ECFEFF', color: '#0891B2', label: 'Web'        },
        };
        const key = String(rawSource).trim().toLowerCase();
        const s = SRC[key] || {
          bg: '#F4F4F5', color: '#71717A',
          label: String(rawSource).replace(/\b\w/g, (c) => c.toUpperCase())
        };
        return `<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:${s.bg}; color:${s.color}; white-space:nowrap;">${s.label}</span>`;
      }

      async function loadAssignedRides(showLoading = false) {
        try {
          // Fetch all rides (use high limit to get all rides for client-side filtering)
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }

          const result = await response.json();
          
          // Check if API returned success
          if (!result.success) {
            throw new Error(result.error || 'Failed to fetch rides');
          }
          
          const rides = (result && result.data ? result.data : []).filter(isDispatcherSourcedRide);

          const assignedRides = rides.filter(isAssignedTabRide);

          // Update cache
          currentRidesData.assigned = assignedRides;

          updateAssignedTabCount(assignedRides.length);
          if (currentTab === 'assigned') {
            updatePaginationInfo(assignedRides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading assigned rides:', error);
          updateAssignedTabCount(0);
        }
      }

      function populateAssignedTable(rides) {
        const tbody = document.getElementById('assignedRidesBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!rides || rides.length === 0) {
          tbody.innerHTML =
            '<tr><td colspan="10" class="text-center py-4 text-muted">No assigned rides to show</td></tr>';
          return;
        }

        rides.forEach((ride) => {
          const name = ride.passenger_name || 'N/A';
          const orderTime = getRideDisplayTime(ride);
          const pickup =
            ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination =
            ride.dest_addr || ride.actual_end_addr || 'N/A';
          const status = ride.status || 'N/A';
          const fare = formatFare(ride.fare_eur, ride.estimate_fare);
          const rawFare = ride.fare_eur != null ? parseFloat(ride.fare_eur) : (ride.estimate_fare != null ? parseFloat(ride.estimate_fare) : 0);

          // Internal Note is only for rides booked via the "New" (not-in-DB)
          // passenger option on Create Order — those are tagged source
          // "Powercabs Dispatch" (existing-passenger dispatcher orders are
          // tagged plain "dispatcher" and don't get this button).
          const isNewPaxDispatchOrder = String(ride.source || '').trim().toLowerCase() === 'powercabs dispatch';
          const hasNote = !!(ride.internal_note && String(ride.internal_note).trim() !== '');

          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${name}</td>
            <td>${orderTime}</td>
            <td style="white-space:normal; word-break:break-word;">${pickup}</td>
            <td style="white-space:normal; word-break:break-word;">${destination}</td>
            <td>${renderAssignedStatusBadge(ride)}</td>
            <td>${renderPaymentBadge(ride.payment_method)}</td>
            <td>${renderPrebookBadge(ride)}</td>
            <td>${renderSourceBadge(ride.source)}</td>
            <td class="text-end pe-4">${fare}</td>
            <td class="text-end pe-4">
              <div style="display:inline-grid; grid-template-columns:repeat(2, auto); gap:6px; justify-items:start;">
                <button type="button" class="complete-ride-btn" onclick="completeRide('${encodeURIComponent(ride.id)}', ${rawFare})">
                  <i class="bi bi-check-circle-fill"></i>
                  <span>Complete</span>
                </button>
                ${isNewPaxDispatchOrder ? `
                <button type="button" class="add-note-btn" onclick="openInternalNoteModal('${encodeURIComponent(ride.id)}')">
                  <i class="bi bi-sticky${hasNote ? '-fill' : ''}"></i>
                  <span>${hasNote ? 'Edit Note' : 'Add Note'}</span>
                </button>` : ''}
                <button type="button" class="unassign-btn" onclick="unassignRide('${encodeURIComponent(ride.id)}')">
                  <i class="bi bi-person-dash"></i>
                  <span>Unassign</span>
                </button>
                <a href="orderassigned.php?id=${encodeURIComponent(ride.id)}&view=1&from=assigned" class="view-details-btn">
                  <span>View Details</span>
                  <i class="bi bi-chevron-right"></i>
                </a>
              </div>
            </td>
          `;
          tbody.appendChild(row);
        });

        applyPreorderSearchFilterForCurrentTab();
      }

      // Confirm + unassign an assigned ride: reopens it for searching and clears
      // the driver and their live GPS in the DB.
      async function unassignRide(encodedRideId) {
        const rideId = decodeURIComponent(encodedRideId);
        const ok = await showConfirmDialog({
          title: 'Unassign ride?',
          message: 'This will remove the assigned driver and move the ride back to <strong>Unassigned</strong> for re-broadcast. Continue?',
          confirmText: 'Yes, unassign',
          cancelText: 'Cancel'
        });
        if (!ok) return;

        try {
          const response = await fetch('api/unassign_ride.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ride_id: rideId })
          });
          if (response.status === 401) { window.location.href = '/'; return; }
          const result = await response.json();
          if (!response.ok || !result.success) {
            throw new Error(result.error || 'Failed to unassign ride');
          }
          // Refresh tabs/counts immediately so the row leaves the Assigned tab.
          await checkRideStatusChanges();
          showPreorderToast('Ride unassigned and reopened for searching.', 'success');
        } catch (err) {
          console.error('Unassign error:', err);
          showPreorderToast(err.message || 'Failed to unassign ride.', 'error');
        }
      }

      async function completeRide(encodedRideId, fareEur) {
        const rideId = decodeURIComponent(encodedRideId);
        const fareLabel = fareEur > 0 ? `€${parseFloat(fareEur).toFixed(2)}` : 'the estimated fare';
        const ok = await showConfirmDialog({
          title: 'Mark ride as complete?',
          message: `Estimated fare of <strong>${fareLabel}</strong> will be marked as the total charged. This action cannot be undone.`,
          confirmText: 'Yes, complete ride',
          cancelText: 'Cancel',
        });
        if (!ok) return;

        try {
          const response = await fetch('api/complete_ride.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ride_id: rideId, fare_eur: fareEur }),
          });
          if (response.status === 401) { window.location.href = '/'; return; }
          const result = await response.json();
          if (!response.ok || !result.success) {
            throw new Error(result.error || 'Failed to complete ride');
          }
          await checkRideStatusChanges();
          showPreorderToast('Ride marked as completed successfully.', 'success');
        } catch (err) {
          console.error('Complete ride error:', err);
          showPreorderToast(err.message || 'Failed to complete ride.', 'error');
        }
      }

      // Lightweight promise-based confirmation dialog (no external dependency).
      function showConfirmDialog(opts) {
        const o = opts || {};
        return new Promise((resolve) => {
          const overlay = document.createElement('div');
          overlay.style.cssText =
            'position:fixed; inset:0; background:rgba(15,23,42,0.45); z-index:20000; display:flex; align-items:center; justify-content:center; padding:16px;';

          const box = document.createElement('div');
          box.style.cssText =
            'background:#fff; border-radius:14px; max-width:420px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,0.25); overflow:hidden; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;';
          box.innerHTML = `
            <div style="padding:20px 22px 8px;">
              <div style="display:flex; align-items:center; gap:10px;">
                <span style="width:34px; height:34px; border-radius:9px; background:#FFF3E8; color:#f37a20; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <i class="bi bi-exclamation-triangle-fill" style="font-size:16px;"></i>
                </span>
                <div style="font-size:16px; font-weight:700; color:#18181B;">${o.title || 'Are you sure?'}</div>
              </div>
              <div style="margin-top:12px; font-size:13.5px; color:#52525B; line-height:1.5;">${o.message || ''}</div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; padding:16px 22px 18px;">
              <button type="button" data-act="cancel" style="height:38px; padding:0 16px; border-radius:8px; border:1.5px solid #E4E4E7; background:#fff; color:#52525B; font-size:0.8125rem; font-weight:600; cursor:pointer;">${o.cancelText || 'Cancel'}</button>
              <button type="button" data-act="confirm" style="height:38px; padding:0 16px; border-radius:8px; border:1.5px solid #f37a20; background:#f37a20; color:#fff; font-size:0.8125rem; font-weight:700; cursor:pointer;">${o.confirmText || 'Confirm'}</button>
            </div>
          `;
          overlay.appendChild(box);
          document.body.appendChild(overlay);

          const cleanup = (val) => {
            document.removeEventListener('keydown', onKey);
            overlay.remove();
            resolve(val);
          };
          const onKey = (e) => {
            if (e.key === 'Escape') cleanup(false);
            if (e.key === 'Enter') cleanup(true);
          };
          document.addEventListener('keydown', onKey);
          overlay.addEventListener('click', (e) => { if (e.target === overlay) cleanup(false); });
          box.querySelector('[data-act="cancel"]').addEventListener('click', () => cleanup(false));
          box.querySelector('[data-act="confirm"]').addEventListener('click', () => cleanup(true));
        });
      }

      // Minimal toast for action feedback.
      function showPreorderToast(message, type) {
        const toast = document.createElement('div');
        const bg = type === 'error' ? '#DC2626' : '#16A34A';
        toast.style.cssText =
          `position:fixed; bottom:24px; right:24px; z-index:20001; background:${bg}; color:#fff; padding:12px 16px; border-radius:10px; font-size:0.8125rem; font-weight:600; box-shadow:0 10px 30px rgba(0,0,0,0.2); max-width:340px; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.transition = 'opacity 0.4s'; toast.style.opacity = '0'; }, 2600);
        setTimeout(() => toast.remove(), 3100);
      }

      // ── Internal Note (Powercabs Dispatch / new-passenger orders only) ──
      // Same lightweight custom-overlay pattern as showConfirmDialog above,
      // but with a text input instead of a plain yes/no choice.
      function showInternalNoteDialog(existingNote) {
        return new Promise((resolve) => {
          const overlay = document.createElement('div');
          overlay.style.cssText =
            'position:fixed; inset:0; background:rgba(15,23,42,0.45); z-index:20000; display:flex; align-items:center; justify-content:center; padding:16px;';

          const box = document.createElement('div');
          box.style.cssText =
            'background:#fff; border-radius:14px; max-width:460px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,0.25); overflow:hidden; font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;';
          box.innerHTML = `
            <div style="padding:20px 22px 8px;">
              <div style="display:flex; align-items:center; gap:10px;">
                <span style="width:34px; height:34px; border-radius:9px; background:#EEF2FF; color:#4F46E5; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
                  <i class="bi bi-sticky-fill" style="font-size:15px;"></i>
                </span>
                <div style="font-size:16px; font-weight:700; color:#18181B;">Internal Note</div>
              </div>
              <div style="margin-top:6px; font-size:12.5px; color:#A1A1AA; line-height:1.4;">Visible only to dispatch staff — not shared with the passenger or driver.</div>
            </div>
            <div style="padding:14px 22px 4px;">
              <textarea data-el="note" rows="5" placeholder="Add a note for this ride..."
                style="width:100%; border:1.5px solid #EBEBEB; border-radius:8px; padding:10px 12px; font-size:0.8125rem; color:#18181B; resize:vertical; font-family:inherit;"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px; padding:16px 22px 18px;">
              <button type="button" data-act="cancel" style="height:38px; padding:0 16px; border-radius:8px; border:1.5px solid #E4E4E7; background:#fff; color:#52525B; font-size:0.8125rem; font-weight:600; cursor:pointer;">Cancel</button>
              <button type="button" data-act="save" style="height:38px; padding:0 16px; border-radius:8px; border:1.5px solid #4F46E5; background:#4F46E5; color:#fff; font-size:0.8125rem; font-weight:700; cursor:pointer;">Save Note</button>
            </div>
          `;
          overlay.appendChild(box);
          document.body.appendChild(overlay);

          const textarea = box.querySelector('[data-el="note"]');
          textarea.value = existingNote || '';

          const cleanup = (val) => {
            document.removeEventListener('keydown', onKey);
            overlay.remove();
            resolve(val);
          };
          const onKey = (e) => { if (e.key === 'Escape') cleanup(null); };
          document.addEventListener('keydown', onKey);
          overlay.addEventListener('click', (e) => { if (e.target === overlay) cleanup(null); });
          box.querySelector('[data-act="cancel"]').addEventListener('click', () => cleanup(null));
          box.querySelector('[data-act="save"]').addEventListener('click', () => cleanup(textarea.value.trim()));

          setTimeout(() => textarea.focus(), 50);
        });
      }

      async function openInternalNoteModal(encodedRideId) {
        const rideId = decodeURIComponent(encodedRideId);
        const ride = (currentRidesData.assigned || []).find((r) => r.id === rideId);
        const existingNote = (ride && ride.internal_note) || '';

        const note = await showInternalNoteDialog(existingNote);
        if (note === null) return; // cancelled

        try {
          const response = await fetch('api/update_internal_note.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ride_id: rideId, internal_note: note }),
          });
          if (response.status === 401) { window.location.href = '/'; return; }
          const result = await response.json();
          if (!response.ok || !result.success) {
            throw new Error(result.error || 'Failed to save note');
          }
          if (ride) ride.internal_note = note;
          if (currentTab === 'assigned') {
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
          showPreorderToast('Internal note saved.', 'success');
        } catch (err) {
          console.error('Save internal note error:', err);
          showPreorderToast(err.message || 'Failed to save note.', 'error');
        }
      }

      function updateAssignedTabCount(count) {
        const badge = document.getElementById('count-assigned');
        if (badge) badge.textContent = count;
      }

      const ENROUTE_STATUSES = ['enroute','en_route','en-route'];

      const TAB_CONFIG = [
        { btnId: 'tab-unassigned', paneId: 'pane-unassigned', key: 'unassigned', action: true },
        { btnId: 'tab-assigned',   paneId: 'pane-assigned',   key: 'assigned',   action: true },
        { btnId: 'tab-enroute',    paneId: 'pane-enroute',    key: 'enroute',    action: false },
        { btnId: 'tab-on-trip',    paneId: 'pane-ontrip',     key: 'ontrip',     action: false },
        { btnId: 'tab-scheduled',  paneId: 'pane-scheduled',  key: 'scheduled',  action: false },
        { btnId: 'tab-cancelled',  paneId: 'pane-cancelled',  key: 'cancelled',  action: false },
        { btnId: 'tab-finished',   paneId: 'pane-completed',  key: 'completed',  action: false },
        { btnId: 'tab-meet-greet', paneId: 'pane-meetgreet',  key: 'meetgreet',  action: true },
      ];

      function setupTabSwitching() {
        function clearAllTabs() {
          TAB_CONFIG.forEach(({ btnId, paneId }) => {
            const btn = document.getElementById(btnId);
            const pane = document.getElementById(paneId);
            if (btn) {
              btn.classList.remove('active-tab', 'is-active');
              btn.setAttribute('aria-selected', 'false');
            }
            if (pane) pane.style.display = 'none';
          });
        }

        TAB_CONFIG.forEach(({ btnId, paneId, key, action }) => {
          const btn = document.getElementById(btnId);
          const pane = document.getElementById(paneId);
          if (!btn) return;
          btn.addEventListener('click', () => {
            currentTab = key;
            clearAllTabs();
            btn.classList.add('active-tab', 'is-active');
            btn.setAttribute('aria-selected', 'true');
            if (pane) pane.style.display = '';
            const rides = currentRidesData[key] || [];
            if (preorderPagination) {
              preorderPagination.update(rides.length, 1);
              updateTableForCurrentTab(1, ITEMS_PER_PAGE);
            }
          });
        });

        // Restore tab from URL hash (e.g. #tab-assigned from back button)
        const hash = window.location.hash;
        if (hash && hash.startsWith('#tab-')) {
          const target = hash.substring(1);
          const tabBtn = document.getElementById(target);
          if (tabBtn) tabBtn.click();
        }
      }

      async function loadScheduledRides(showLoading = false) {
        try {
          // Fetch all rides (use high limit to get all rides for client-side filtering)
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }

          const result = await response.json();
          
          // Check if API returned success
          if (!result.success) {
            throw new Error(result.error || 'Failed to fetch rides');
          }
          
          const rides = (result && result.data ? result.data : []).filter(isDispatcherSourcedRide);

          const scheduledRides = rides.filter(isScheduledTabRide);

          // Update cache
          currentRidesData.scheduled = scheduledRides;

          updateScheduledTabCount(scheduledRides.length);
          if (currentTab === 'scheduled') {
            updatePaginationInfo(scheduledRides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading scheduled rides:', error);
          updateScheduledTabCount(0);
        }
      }

      function scheduledCategoryBadge(source) {
        const src = (source || '').toLowerCase();
        if (src.includes('meet_and_greet') || src.includes('meet and greet')) {
          return '<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:#EFF6FF; color:#2563EB; white-space:nowrap;">M&amp;G</span>';
        }
        if (src.startsWith('corporate') || src.includes('corporate')) {
          return '<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:#F0FDF4; color:#16A34A; white-space:nowrap;">Corporate</span>';
        }
        if (src === 'dispatcher') {
          return '<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:#F5F3FF; color:#7C3AED; white-space:nowrap;">Dispatcher</span>';
        }
        return '<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:#FFF7ED; color:#EA580C; white-space:nowrap;">App</span>';
      }

      function populateScheduledTable(rides) {
        const tbody = document.getElementById('scheduledRidesBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!rides || rides.length === 0) {
          tbody.innerHTML =
            '<tr><td colspan="10" class="text-center py-4 text-muted">No pre-orders to show</td></tr>';
          return;
        }

        rides.forEach((ride) => {
          const name = ride.passenger_name || 'N/A';
          const pickupTime = formatScheduledTime(ride.scheduled_at, ride.meta);
          const pickup      = ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination = ride.dest_addr   || ride.actual_end_addr   || 'N/A';
          const status = ride.status || 'N/A';
          const fare   = formatFare(ride.fare_eur, ride.estimate_fare);
          const rideId = ride.id || '';
          const categoryBadge = scheduledCategoryBadge(ride.source);

          const hasDriver = !!(ride.driver_id);
          const actionCell = hasDriver
            ? `<a href="orderassigned.php?id=${encodeURIComponent(rideId)}&view=1&from=scheduled" class="view-details-btn">
                 <span>View Details</span><i class="bi bi-chevron-right"></i>
               </a>`
            : `<a href="orderassigned.php?id=${encodeURIComponent(rideId)}&from=scheduled" class="view-details-btn"
                 style="background:#f37a20 !important; color:#fff !important; border-color:#f37a20 !important;">
                 <span>Assign</span><i class="bi bi-chevron-right"></i>
               </a>`;

          // Dispatch reminder — only offered for unassigned pre-orders.
          // Renders a button; on click it shows a fixed reminder line in-cell
          // and reads it aloud with the browser's built-in voice (free, offline).
          const voiceMsg = buildPreorderVoiceMessage(name, pickup, destination);
          const aiCell = hasDriver
            ? '<span class="text-muted" style="font-size:0.8rem;">—</span>'
            : `<span class="ai-suggest-wrap">
                 <button type="button" class="ai-suggest-btn" data-msg="${encodeURIComponent(voiceMsg)}" title="Read the assignment reminder aloud">
                   <i class="bi bi-stars"></i><span>AI tip</span>
                 </button>
                 <div class="ai-suggest-text" style="display:none;"></div>
               </span>`;

          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${name}</td>
            <td>${pickupTime}</td>
            <td>${pickup}</td>
            <td>${destination}</td>
            <td>${renderStatusBadge(status)}</td>
            <td>${renderPaymentBadge(ride.payment_method)}</td>
            <td>${categoryBadge}</td>
            <td class="text-end pe-4">${fare}</td>
            <td class="text-nowrap">${aiCell}</td>
            <td class="text-end pe-4">${actionCell}</td>
          `;
          tbody.appendChild(row);
        });

        applyPreorderSearchFilterForCurrentTab();
      }

      function updateScheduledTabCount(count) {
        const badge = document.getElementById('count-scheduled');
        if (badge) badge.textContent = count;
      }

      // ── Spoken assignment reminders for unassigned pre-orders ──────────
      // Builds a fixed reminder line from the ride's own fields (no API/AI
      // call) and reads it aloud with the browser's built-in voice.
      function buildPreorderVoiceMessage(name, pickup, destination) {
        const who  = name && name !== 'N/A' ? name : 'a passenger';
        const from = pickup && pickup !== 'N/A' ? `, pickup at ${pickup}` : '';
        const to   = destination && destination !== 'N/A' ? `, going to ${destination}` : '';
        return `Pre-order for ${who}${from}${to}. No driver assigned yet. Please assign a driver to this ride.`;
      }

      function speakSuggestion(text) {
        if (!('speechSynthesis' in window) || !text) return;
        try {
          window.speechSynthesis.cancel(); // stop any in-flight utterance
          const u = new SpeechSynthesisUtterance(text);
          u.rate = 1.0; u.pitch = 1.0; u.lang = 'en-GB';
          window.speechSynthesis.speak(u);
        } catch (e) {
          console.warn('Speech synthesis failed:', e);
        }
      }

      function showSuggestionText(box, text) {
        box.classList.remove('is-error');
        box.style.display = 'block';
        box.innerHTML =
          `<i class="bi bi-stars" style="color:#7C3AED;"></i> ${escapeHtmlSafe(text)}` +
          `<button type="button" class="ai-replay" title="Read aloud again"><i class="bi bi-volume-up-fill"></i></button>`;
        const replay = box.querySelector('.ai-replay');
        if (replay) replay.addEventListener('click', () => speakSuggestion(text));
      }

      function escapeHtmlSafe(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
      }

      function closeAllSuggestionPopovers(except) {
        document.querySelectorAll('.ai-suggest-text').forEach((b) => {
          if (b !== except) b.style.display = 'none';
        });
      }

      function handleAiSuggestClick(btn) {
        const msg = btn.dataset.msg ? decodeURIComponent(btn.dataset.msg) : '';
        if (!msg) return;
        const box = btn.parentElement.querySelector('.ai-suggest-text');

        // Toggle: a second click on the same button closes it (and stops the voice).
        if (box.style.display === 'block') {
          box.style.display = 'none';
          if ('speechSynthesis' in window) window.speechSynthesis.cancel();
          return;
        }
        closeAllSuggestionPopovers(box);
        showSuggestionText(box, msg);
        speakSuggestion(msg);
      }

      // Delegated listener — survives table re-renders on every poll.
      document.addEventListener('click', (e) => {
        const btn = e.target.closest('.ai-suggest-btn');
        if (btn) { handleAiSuggestClick(btn); return; }
        // Click outside any popover/button → close them all.
        if (!e.target.closest('.ai-suggest-text')) closeAllSuggestionPopovers(null);
      });

      async function loadCancelledRides(showLoading = false) {
        try {
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }
          const result = await response.json();
          if (!result.success) {
            throw new Error(result.error || 'Failed to fetch rides');
          }
          const rides = (result && result.data ? result.data : []).filter(isDispatcherSourcedRide);
          const cancelledRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'cancelled' || status === 'canceled';
          });
          currentRidesData.cancelled = cancelledRides;
          updateCancelledTabCount(cancelledRides.length);
          if (currentTab === 'cancelled') {
            updatePaginationInfo(cancelledRides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading cancelled rides:', error);
          updateCancelledTabCount(0);
        }
      }

      function populateCancelledTable(rides) {
        const tbody = document.getElementById('cancelledRidesBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!rides || rides.length === 0) {
          tbody.innerHTML =
            '<tr><td colspan="7" class="text-center py-4 text-muted">No cancelled rides to show</td></tr>';
          return;
        }
        rides.forEach((ride) => {
          const name = ride.passenger_name || 'N/A';
          const orderTime = getRideDisplayTime(ride);
          const pickup = ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination = ride.dest_addr || ride.actual_end_addr || 'N/A';
          const status = ride.status || 'N/A';
          const fare = formatFare(ride.fare_eur, ride.estimate_fare);
          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${name}</td>
            <td>${orderTime}</td>
            <td>${pickup}</td>
            <td>${destination}</td>
            <td>${renderStatusBadge(status)}</td>
            <td>${renderPaymentBadge(ride.payment_method)}</td>
            <td class="text-end pe-4">${fare}</td>
          `;
          tbody.appendChild(row);
        });

        applyPreorderSearchFilterForCurrentTab();
      }

      function updateCancelledTabCount(count) {
        const badge = document.getElementById('count-cancelled');
        if (badge) badge.textContent = count;
      }

      async function loadCompletedRides(showLoading = false) {
        try {
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) {
            throw new Error('Failed to fetch rides from server');
          }
          const result = await response.json();
          if (!result.success) {
            throw new Error(result.error || 'Failed to fetch rides');
          }
          const rides = (result && result.data ? result.data : []).filter(isDispatcherSourcedRide);
          const completedRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return status === 'completed' || status === 'finished';
          });
          currentRidesData.completed = completedRides;
          updateCompletedTabCount(completedRides.length);
          if (currentTab === 'completed') {
            updatePaginationInfo(completedRides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading completed rides:', error);
          updateCompletedTabCount(0);
        }
      }

      function populateCompletedTable(rides) {
        const tbody = document.getElementById('completedRidesBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!rides || rides.length === 0) {
          tbody.innerHTML =
            '<tr><td colspan="11" class="text-center py-4 text-muted">No completed rides to show</td></tr>';
          return;
        }
        rides.forEach((ride) => {
          const name = ride.passenger_name || 'N/A';
          const orderTime = getRideDisplayTime(ride);
          const pickup = ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination = ride.dest_addr || ride.actual_end_addr || 'N/A';
          const status = ride.status || 'N/A';
          const finalFare = formatFare(ride.final_fare, null);
          const tollAmount = formatFare(ride.toll_amount, null);
          const totalCharged = formatFare(ride.total_charged, null);
          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${name}</td>
            <td>${orderTime}</td>
            <td>${pickup}</td>
            <td>${destination}</td>
            <td>${renderStatusBadge(status)}</td>
            <td>${renderPaymentBadge(ride.payment_method)}</td>
            <td>${renderPrebookBadge(ride)}</td>
            <td>${renderSourceBadge(ride.source)}</td>
            <td class="text-end pe-4">${finalFare}</td>
            <td class="text-end pe-4">${tollAmount}</td>
            <td class="text-end pe-4">${totalCharged}</td>
          `;
          tbody.appendChild(row);
        });

        applyPreorderSearchFilterForCurrentTab();
      }

      function setupPreorderSearch() {
        const searchInput = document.getElementById('globalSearchInput');
        if (!searchInput) return;

        searchInput.addEventListener('input', function () {
          applyPreorderSearchFilterForCurrentTab();
        });
      }

      function applyPreorderSearchFilterForCurrentTab() {
        const searchInput = document.getElementById('globalSearchInput');
        if (!searchInput) return;

        const term = searchInput.value.trim().toLowerCase();

        let tbodyId = null;
        if (currentTab === 'unassigned') {
          tbodyId = 'unassignedRidesBody';
        } else if (currentTab === 'assigned') {
          tbodyId = 'assignedRidesBody';
        } else if (currentTab === 'ontrip') {
          tbodyId = 'onTripRidesBody';
        } else if (currentTab === 'scheduled') {
          tbodyId = 'scheduledRidesBody';
        } else if (currentTab === 'cancelled') {
          tbodyId = 'cancelledRidesBody';
        } else if (currentTab === 'completed') {
          tbodyId = 'completedRidesBody';
        } else if (currentTab === 'meetgreet') {
          tbodyId = 'meetGreetRidesBody';
        }

        if (!tbodyId) return;

        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;

        const rows = tbody.querySelectorAll('tr');

        if (!term) {
          rows.forEach(row => {
            row.style.display = '';
          });
          return;
        }

        rows.forEach(row => {
          const cells = row.querySelectorAll('td');
          if (cells.length < 5) {
            row.style.display = '';
            return;
          }

          const name = cells[0].textContent.toLowerCase();
          const orderTime = cells[1].textContent.toLowerCase();
          const pickup = cells[2].textContent.toLowerCase();
          const destination = cells[3].textContent.toLowerCase();
          const status = cells[4].textContent.toLowerCase();

          const matches =
            name.includes(term) ||
            orderTime.includes(term) ||
            pickup.includes(term) ||
            destination.includes(term) ||
            status.includes(term);

          row.style.display = matches ? '' : 'none';
        });
      }

      function updateCompletedTabCount(count) {
        const badge = document.getElementById('count-finished');
        if (badge) badge.textContent = count;
      }

      async function loadEnrouteRides(showLoading = false) {
        try {
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) throw new Error('Failed to fetch rides');
          const result = await response.json();
          if (!result.success) throw new Error(result.error || 'Failed');
          const rides = (result && result.data ? result.data : []).filter(isDispatcherSourcedRide);
          const enrouteRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return ENROUTE_STATUSES.includes(status);
          });
          currentRidesData.enroute = enrouteRides;
          updateEnrouteTabCount(enrouteRides.length);
          if (currentTab === 'enroute') {
            updatePaginationInfo(enrouteRides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading enroute rides:', error);
          updateEnrouteTabCount(0);
        }
      }

      async function loadOnTripRides(showLoading = false) {
        try {
          const response = await fetch('api/get_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) throw new Error('Failed to fetch rides from server');
          const result = await response.json();
          if (!result.success) throw new Error(result.error || 'Failed to fetch rides');
          const rides = (result && result.data ? result.data : []).filter(isDispatcherSourcedRide);
          const onTripRides = rides.filter((ride) => {
            const status = (ride.status || '').toLowerCase();
            return ['on_trip','ongoing','in_progress','ontrip','started','arrived_at_pickup','driver_arrived','arrived'].includes(status);
          });
          currentRidesData.ontrip = onTripRides;
          updateOnTripTabCount(onTripRides.length);
          if (currentTab === 'ontrip') {
            updatePaginationInfo(onTripRides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading on-trip rides:', error);
          updateOnTripTabCount(0);
        }
      }

      function populateEnrouteTable(rides) {
        const tbody = document.getElementById('enrouteRidesBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!rides || rides.length === 0) {
          tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No enroute rides to show</td></tr>';
          return;
        }
        rides.forEach((ride) => {
          const name = ride.passenger_name || 'N/A';
          const orderTime = getRideDisplayTime(ride);
          const pickup = ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination = ride.dest_addr || ride.actual_end_addr || 'N/A';
          const status = ride.status || 'N/A';
          const driverName = ride.driver_name || 'Unassigned';
          const fare = formatFare(ride.fare_eur, ride.estimate_fare);
          const rideId = encodeURIComponent(ride.id || '');
          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${name}</td>
            <td>${orderTime}</td>
            <td>${pickup}</td>
            <td>${destination}</td>
            <td>${renderStatusBadge(status)}</td>
            <td>${renderPaymentBadge(ride.payment_method)}</td>
            <td>${renderSourceBadge(ride.source)}</td>
            <td>${driverName}</td>
            <td>${fare}</td>
            <td class="text-end pe-4">
              <a href="orderassigned.php?id=${rideId}&view=1&from=enroute" class="view-details-btn">
                <span>View Live</span>
                <i class="bi bi-geo-alt-fill"></i>
              </a>
            </td>
          `;
          tbody.appendChild(row);
        });
        applyPreorderSearchFilterForCurrentTab();
      }

      function updateEnrouteTabCount(count) {
        const badge = document.getElementById('count-enroute');
        if (badge) badge.textContent = count;
      }

      function populateOnTripTable(rides) {
        const tbody = document.getElementById('onTripRidesBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!rides || rides.length === 0) {
          tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-muted">No on-trip rides to show</td></tr>';
          return;
        }
        rides.forEach((ride) => {
          const name = ride.passenger_name || 'N/A';
          const orderTime = getRideDisplayTime(ride);
          const pickup = ride.pickup_addr || ride.actual_start_addr || 'N/A';
          const destination = ride.dest_addr || ride.actual_end_addr || 'N/A';
          const status = ride.status || 'N/A';
          const driverName = ride.driver_name || 'Unassigned';
          const fare = formatFare(ride.fare_eur, ride.estimate_fare);
          const rideId = encodeURIComponent(ride.id || '');
          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${name}</td>
            <td>${orderTime}</td>
            <td>${pickup}</td>
            <td>${destination}</td>
            <td>${renderStatusBadge(status)}</td>
            <td>${renderPaymentBadge(ride.payment_method)}</td>
            <td>${renderPrebookBadge(ride)}</td>
            <td>${renderSourceBadge(ride.source)}</td>
            <td>${driverName}</td>
            <td>${fare}</td>
            <td class="text-end pe-4">
              <a href="orderassigned.php?id=${rideId}&view=1&from=on-trip" class="view-details-btn">
                <span>View Live</span>
                <i class="bi bi-geo-alt-fill"></i>
              </a>
            </td>
          `;
          tbody.appendChild(row);
        });
        applyPreorderSearchFilterForCurrentTab();
      }

      function updateOnTripTabCount(count) {
        const tab = document.getElementById('tab-on-trip');
        if (!tab) return;
        const badge = tab.querySelector('#count-on-trip');
        if (badge) badge.textContent = count;
      }

      // ──────────────── Meet & Greet ────────────────
      async function loadMeetGreetRides(showLoading = false) {
        const tbody = document.getElementById('meetGreetRidesBody');
        if (showLoading && tbody) {
          tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">Loading rides...</td></tr>';
        }
        try {
          const response = await fetch('api/get_meet_and_greet_rides.php?page=1&limit=1000');
          if (response.status === 401) { window.location.href = '/'; return; }
          if (!response.ok) throw new Error('Failed to fetch meet & greet rides');
          const result = await response.json();
          if (!result.success) throw new Error(result.error || 'Failed to fetch meet & greet rides');
          // Exclude cancelled rides — they belong only in the Cancelled tab
          const rides = (result && result.data ? result.data : []).filter(r => {
            const s = (r.status || '').toLowerCase();
            return s !== 'cancelled' && s !== 'canceled';
          });

          currentRidesData.meetgreet = rides;
          updateMeetGreetTabCount(rides.length);

          if (currentTab === 'meetgreet') {
            updatePaginationInfo(rides.length);
            const currentPage = preorderPagination ? preorderPagination.getCurrentPage() : 1;
            updateTableForCurrentTab(currentPage, ITEMS_PER_PAGE);
          }
        } catch (error) {
          console.error('Error loading meet & greet rides:', error);
          updateMeetGreetTabCount(0);
          if (tbody && currentTab === 'meetgreet') {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Error loading rides. Please refresh.</td></tr>';
          }
        }
      }

      function populateMeetGreetTable(rides) {
        const tbody = document.getElementById('meetGreetRidesBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!rides || rides.length === 0) {
          tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-muted">No meet &amp; greet rides to show</td></tr>';
          return;
        }

        const esc = (v) => String(v ?? '')
          .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;').replace(/'/g, '&#039;');

        rides.forEach((ride) => {
          const company     = esc(ride.company || '—');
          const employee    = esc(ride.employee || ride.passenger_name || '—');
          const orderTime   = getRideDisplayTime(ride);
          const pickup      = esc(ride.pickup_addr || '—');
          const destination = esc(ride.dest_addr || '—');
          const payment     = esc(ride.payment_method || '—');
          const fare        = formatFare(ride.fare_eur, ride.estimate_fare);
          const status      = ride.status || 'N/A';
          const rideId      = encodeURIComponent(ride.id || '');
          const statusKey   = String(status).trim().toLowerCase().replace(/\s+/g, '_');

          let actionCell = '';
          if (statusKey === 'pending' || statusKey === 'searching') {
            actionCell = `<a href="orderassigned.php?corp_id=${rideId}" class="view-details-btn">
                <span>Assign</span><i class="bi bi-chevron-right"></i></a>`;
          } else if (['assigned','on_trip','ongoing','in_progress','ontrip','started','arrived_at_pickup','driver_arrived','arrived','enroute','en_route','en-route','completed','cancelled'].includes(statusKey)) {
            actionCell = `<a href="orderassigned.php?corp_id=${rideId}&view=1" class="view-details-btn">
                <span>View Details</span><i class="bi bi-chevron-right"></i></a>`;
          }

          const row = document.createElement('tr');
          row.innerHTML = `
            <td class="ps-3">${company}</td>
            <td>${employee}</td>
            <td>${orderTime}</td>
            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${pickup}">${pickup}</td>
            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${destination}">${destination}</td>
            <td>${renderPaymentBadge(ride.payment_method)}</td>
            <td class="text-end pe-2">${fare}</td>
            <td>${renderStatusBadge(status)}</td>
            <td class="text-end pe-4">${actionCell}</td>
          `;
          tbody.appendChild(row);
        });

        applyPreorderSearchFilterForCurrentTab();
      }

      function updateMeetGreetTabCount(count) {
        const badge = document.getElementById('count-meet-greet');
        if (badge) badge.textContent = count;
      }

    </script>
  </body>
</html>
