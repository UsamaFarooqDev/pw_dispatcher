// Reusable Pagination Component
class PaginationManager {
  constructor(config) {
    this.config = {
      containerId: config.containerId,
      page: config.page || 1,
      limit: config.limit || 10,
      total: config.total || 0,
      onPageChange: config.onPageChange || (() => {}),
      showInfo: config.showInfo !== false,
      maxVisiblePages: config.maxVisiblePages || 5,
      ...config
    };

    this.currentPage = this.config.page;
    this.totalPages = Math.ceil(this.config.total / this.config.limit);

    PaginationManager.injectStylesOnce();
  }

  static injectStylesOnce() {
    if (document.getElementById('pagination-manager-styles')) return;
    const style = document.createElement('style');
    style.id = 'pagination-manager-styles';
    style.textContent = `
      .pm-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
      }
      .pm-info {
        font-size: 0.8125rem;
        color: #71717A;
        font-weight: 500;
      }
      .pm-info strong { color: #18181B; font-weight: 600; }
      .pm-nav {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px;
        background: #FAFAFA;
        border: 1px solid #E4E4E7;
        border-radius: 10px;
      }
      .pm-btn {
        min-width: 34px;
        height: 34px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 7px;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #52525B;
        cursor: pointer;
        transition: background 0.12s, color 0.12s, border-color 0.12s, box-shadow 0.12s;
        user-select: none;
      }
      .pm-btn:hover:not(:disabled):not(.pm-active):not(.pm-ellipsis) {
        background: #fff;
        color: #18181B;
        border-color: #E4E4E7;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
      }
      .pm-btn:disabled {
        color: #D4D4D8;
        cursor: not-allowed;
      }
      .pm-btn.pm-active {
        background: #f37a20;
        color: #fff;
        border-color: #f37a20;
        box-shadow: 0 2px 6px rgba(243,122,32,0.30);
        cursor: default;
      }
      .pm-btn.pm-ellipsis {
        color: #A1A1AA;
        cursor: default;
        min-width: 24px;
        padding: 0 4px;
      }
      .pm-btn i { font-size: 13px; line-height: 1; }
      .pm-step { padding: 0 12px; }
      @media (max-width: 575.98px) {
        .pm-pagination { justify-content: center; }
        .pm-info { width: 100%; text-align: center; }
        .pm-step span { display: none; }
        .pm-step { padding: 0 8px; min-width: 34px; }
      }
    `;
    document.head.appendChild(style);
  }

  update(total, page = null) {
    this.config.total = total;
    if (page !== null) this.currentPage = page;
    this.totalPages = Math.ceil(total / this.config.limit);
    if (this.currentPage > this.totalPages && this.totalPages > 0) {
      this.currentPage = this.totalPages;
    }
    this.render();
  }

  render() {
    const container = document.getElementById(this.config.containerId);
    if (!container) return;

    const { total, limit, showInfo } = this.config;
    const start = total === 0 ? 0 : (this.currentPage - 1) * limit + 1;
    const end = Math.min(this.currentPage * limit, total);

    container.innerHTML = '';
    const wrapper = document.createElement('div');
    wrapper.className = 'pm-pagination';

    if (showInfo) {
      const infoDiv = document.createElement('div');
      infoDiv.className = 'pm-info';
      infoDiv.innerHTML = total === 0
        ? 'No results'
        : `Showing <strong>${start}</strong>–<strong>${end}</strong> of <strong>${total}</strong>`;
      wrapper.appendChild(infoDiv);
    }

    const nav = document.createElement('div');
    nav.className = 'pm-nav';
    nav.setAttribute('role', 'navigation');
    nav.setAttribute('aria-label', 'Pagination');

    nav.appendChild(this.createStepButton('prev'));

    const pages = this.getPageNumbers();
    pages.forEach(pageNum => {
      if (pageNum === '...') {
        const span = document.createElement('span');
        span.className = 'pm-btn pm-ellipsis';
        span.innerHTML = '&hellip;';
        nav.appendChild(span);
      } else {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pm-btn';
        if (pageNum === this.currentPage) {
          btn.classList.add('pm-active');
          btn.setAttribute('aria-current', 'page');
        }
        btn.textContent = pageNum;
        btn.addEventListener('click', () => this.goToPage(pageNum));
        nav.appendChild(btn);
      }
    });

    nav.appendChild(this.createStepButton('next'));

    wrapper.appendChild(nav);
    container.appendChild(wrapper);
  }

  createStepButton(direction) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'pm-btn pm-step';
    const isPrev = direction === 'prev';
    const disabled = isPrev
      ? this.currentPage === 1
      : (this.currentPage === this.totalPages || this.totalPages === 0);
    btn.disabled = disabled;
    btn.setAttribute('aria-label', isPrev ? 'Previous page' : 'Next page');
    btn.innerHTML = isPrev
      ? '<i class="bi bi-chevron-left"></i><span>Prev</span>'
      : '<span>Next</span><i class="bi bi-chevron-right"></i>';
    btn.addEventListener('click', () => {
      this.goToPage(this.currentPage + (isPrev ? -1 : 1));
    });
    return btn;
  }

  getPageNumbers() {
    const { maxVisiblePages } = this.config;
    const total = this.totalPages;
    const current = this.currentPage;
    const pages = [];

    if (total === 0) return pages;

    if (total <= maxVisiblePages) {
      for (let i = 1; i <= total; i++) pages.push(i);
      return pages;
    }

    pages.push(1);

    let start = Math.max(2, current - Math.floor(maxVisiblePages / 2));
    let end = Math.min(total - 1, start + maxVisiblePages - 3);

    if (end === total - 1) {
      start = Math.max(2, total - maxVisiblePages + 2);
    }

    if (start > 2) pages.push('...');
    for (let i = start; i <= end; i++) pages.push(i);
    if (end < total - 1) pages.push('...');

    if (total > 1) pages.push(total);
    return pages;
  }

  goToPage(page) {
    if (page < 1 || page > this.totalPages || page === this.currentPage) return;
    this.currentPage = page;
    this.render();
    this.config.onPageChange(page, this.config.limit);
  }

  getCurrentPage() { return this.currentPage; }
  getLimit() { return this.config.limit; }
}

if (typeof module !== 'undefined' && module.exports) {
  module.exports = PaginationManager;
}
