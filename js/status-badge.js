// Shared status badge configuration for consistent styling across all pages.
const STATUS_CONFIG = {
  completed:  { bg: '#F0FDF4', color: '#16A34A', label: 'Completed'  },
  finished:   { bg: '#F0FDF4', color: '#16A34A', label: 'Finished'   },
  assigned:   { bg: '#EFF6FF', color: '#2563EB', label: 'Assigned'   },
  searching:  { bg: '#FFF3E8', color: '#f37a20', label: 'Searching'  },
  pending:    { bg: '#FFF3E8', color: '#f37a20', label: 'Pending'    },
  waiting:    { bg: '#FFF3E8', color: '#f37a20', label: 'Waiting'    },
  upcoming:   { bg: '#F5F3FF', color: '#7C3AED', label: 'Upcoming'   },
  scheduled:  { bg: '#F5F3FF', color: '#7C3AED', label: 'Scheduled'  },
  cancelled:  { bg: '#FFF1F2', color: '#E11D48', label: 'Cancelled'  },
  canceled:   { bg: '#FFF1F2', color: '#E11D48', label: 'Cancelled'  },
  on_trip:    { bg: '#FFF7ED', color: '#EA580C', label: 'On Trip'    },
  in_progress:{ bg: '#FFF7ED', color: '#EA580C', label: 'In Progress'},
  on_progress:{ bg: '#FFF7ED', color: '#EA580C', label: 'On Progress'},
  active:     { bg: '#EFF6FF', color: '#2563EB', label: 'Active'     },
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
