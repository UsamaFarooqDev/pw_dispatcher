// Shared status badge configuration for consistent styling across all pages.
const STATUS_CONFIG = {
  // ── Terminal states ────────────────────────────────────────────────────
  completed:          { bg: '#F0FDF4', color: '#16A34A', label: 'Completed'        },
  finished:           { bg: '#F0FDF4', color: '#16A34A', label: 'Completed'        },
  cancelled:          { bg: '#FFF1F2', color: '#E11D48', label: 'Cancelled'        },
  canceled:           { bg: '#FFF1F2', color: '#E11D48', label: 'Cancelled'        },
  // ── Assigned (driver confirmed) ────────────────────────────────────────
  assigned:           { bg: '#EFF6FF', color: '#2563EB', label: 'Assigned'         },
  driver_assigned:    { bg: '#EFF6FF', color: '#2563EB', label: 'Assigned'         },
  driver_accepted:    { bg: '#EFF6FF', color: '#2563EB', label: 'Assigned'         },
  accepted:           { bg: '#EFF6FF', color: '#2563EB', label: 'Assigned'         },
  // ── Enroute (driver heading to pickup) ──────────────────────────────────
  enroute:            { bg: '#EEF2FF', color: '#4F46E5', label: 'Enroute'          },
  en_route:           { bg: '#EEF2FF', color: '#4F46E5', label: 'Enroute'          },
  // ── Active / on-trip states ────────────────────────────────────────────
  on_trip:            { bg: '#FFF7ED', color: '#EA580C', label: 'On Trip'          },
  ontrip:             { bg: '#FFF7ED', color: '#EA580C', label: 'On Trip'          },
  ongoing:            { bg: '#FFF7ED', color: '#EA580C', label: 'On Trip'          },
  in_progress:        { bg: '#FFF7ED', color: '#EA580C', label: 'In Progress'      },
  on_progress:        { bg: '#FFF7ED', color: '#EA580C', label: 'In Progress'      },
  started:            { bg: '#FFF7ED', color: '#EA580C', label: 'On Trip'          },
  arrived_at_pickup:  { bg: '#F0FDF4', color: '#16A34A', label: 'Arrived'          },
  driver_arrived:     { bg: '#F0FDF4', color: '#16A34A', label: 'Arrived'          },
  arrived:            { bg: '#F0FDF4', color: '#16A34A', label: 'Arrived'          },
  // ── Pre-order / scheduled ─────────────────────────────────────────────
  scheduled:          { bg: '#F5F3FF', color: '#7C3AED', label: 'Scheduled'        },
  upcoming:           { bg: '#F5F3FF', color: '#7C3AED', label: 'Upcoming'         },
  awaiting_assignment:{ bg: '#F5F3FF', color: '#7C3AED', label: 'Pre-Order'        },
  // ── Unassigned / searching ─────────────────────────────────────────────
  searching:          { bg: '#FFF3E8', color: '#f37a20', label: 'Searching'        },
  pending:            { bg: '#FFF3E8', color: '#f37a20', label: 'Pending'          },
  waiting:            { bg: '#FFF3E8', color: '#f37a20', label: 'Waiting'          },
  active:             { bg: '#EFF6FF', color: '#2563EB', label: 'Active'           },
};

/**
 * Returns an HTML string for a styled status pill badge.
 * @param {string} rawStatus - The raw status string from the API.
 * @returns {string} HTML string for the badge.
 */
function renderStatusBadge(rawStatus) {
  const key = String(rawStatus ?? '').trim().toLowerCase().replace(/\s+/g, '_');
  const s = STATUS_CONFIG[key] || { bg: '#F4F4F5', color: '#71717A', label: rawStatus || '-' };
  return `<span class="rounded-pill px-2 py-1 fw-semibold" style="font-size:0.72rem; background:${s.bg}; color:${s.color}; white-space:nowrap;">${s.label}</span>`;
}

// Payment-method badge configuration (cash, prepaid/online card, etc.)
const PAYMENT_CONFIG = {
  cash:    { bg: '#F0FDF4', color: '#16A34A', label: 'Cash',    icon: 'bi-cash-coin'             },
  prepaid: { bg: '#EEF2FF', color: '#4F46E5', label: 'Prepaid', icon: 'bi-credit-card-2-front'   },
  stripe:  { bg: '#EEF2FF', color: '#635BFF', label: 'Stripe',  icon: 'bi-credit-card'           },
  card:    { bg: '#EFF6FF', color: '#2563EB', label: 'Card',    icon: 'bi-credit-card'           },
  online:  { bg: '#EEF2FF', color: '#4F46E5', label: 'Online',  icon: 'bi-globe'                 },
};

/**
 * Returns an HTML string for a styled payment-method pill badge.
 * @param {string} rawMethod - The raw payment_method string from the API.
 * @returns {string} HTML string for the badge (em-dash when empty).
 */
function renderPaymentBadge(rawMethod) {
  if (rawMethod === null || rawMethod === undefined || String(rawMethod).trim() === '') {
    return '<span style="color:#A1A1AA; font-size:0.72rem;">—</span>';
  }
  const key = String(rawMethod).trim().toLowerCase().replace(/\s+/g, '_');
  const p = PAYMENT_CONFIG[key] || {
    bg: '#F4F4F5', color: '#71717A',
    label: String(rawMethod).replace(/\b\w/g, (c) => c.toUpperCase()),
    icon: 'bi-wallet2'
  };
  return `<span class="rounded-pill px-2 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size:0.72rem; background:${p.bg}; color:${p.color}; white-space:nowrap;"><i class="bi ${p.icon}" style="font-size:0.72rem;"></i>${p.label}</span>`;
}
