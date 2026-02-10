// Reusable Pagination Component
class PaginationManager {
  constructor(config) {
    this.config = {
      containerId: config.containerId, // ID of pagination container
      page: config.page || 1,
      limit: config.limit || 10,
      total: config.total || 0,
      onPageChange: config.onPageChange || (() => {}),
      showInfo: config.showInfo !== false, // Show "1-10 of 100 items"
      maxVisiblePages: config.maxVisiblePages || 5, // Max page numbers to show
      ...config
    };
    
    this.currentPage = this.config.page;
    this.totalPages = Math.ceil(this.config.total / this.config.limit);
  }

  update(total, page = null) {
    this.config.total = total;
    if (page !== null) {
      this.currentPage = page;
    }
    this.totalPages = Math.ceil(total / this.config.limit);
    this.render();
  }

  render() {
    const container = document.getElementById(this.config.containerId);
    if (!container) return;

    const { total, limit, showInfo } = this.config;
    const start = total === 0 ? 0 : (this.currentPage - 1) * limit + 1;
    const end = Math.min(this.currentPage * limit, total);

    // Clear existing content
    container.innerHTML = '';

    // Create pagination wrapper
    const wrapper = document.createElement('div');
    wrapper.className = 'd-flex justify-content-between align-items-center';

    // Info text
    if (showInfo) {
      const infoDiv = document.createElement('div');
      infoDiv.className = 'text-muted small pagination-info';
      infoDiv.textContent = total === 0 
        ? '0 items' 
        : `${start}–${end} of ${total} items`;
      wrapper.appendChild(infoDiv);
    }

    // Pagination nav
    const nav = document.createElement('nav');
    const ul = document.createElement('ul');
    ul.className = 'pagination mb-0 gap-1';

    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = 'page-item';
    if (this.currentPage === 1) {
      prevLi.classList.add('disabled');
    }
    const prevLink = document.createElement('button');
    prevLink.className = 'page-link text-dark border rounded-0 d-flex align-items-center justify-content-center';
    prevLink.style.cssText = 'width: 32px; height: 32px; padding: 0; background: white; border: 1px solid #ddd; cursor: pointer;';
    prevLink.innerHTML = '‹';
    prevLink.disabled = this.currentPage === 1;
    prevLink.addEventListener('click', () => this.goToPage(this.currentPage - 1));
    prevLi.appendChild(prevLink);
    ul.appendChild(prevLi);

    // Page numbers
    const pages = this.getPageNumbers();
    pages.forEach(pageNum => {
      const li = document.createElement('li');
      li.className = 'page-item';
      if (pageNum === this.currentPage) {
        li.classList.add('active');
      }
      if (pageNum === '...') {
        li.classList.add('disabled');
        const span = document.createElement('span');
        span.className = 'page-link text-dark border rounded-0 d-flex align-items-center justify-content-center';
        span.style.cssText = 'width: 32px; height: 32px; padding: 0; background: white; border: 1px solid #ddd;';
        span.textContent = '...';
        li.appendChild(span);
      } else {
        const link = document.createElement('button');
        link.className = pageNum === this.currentPage 
          ? 'page-link bg-orange border-0 rounded-0 text-white d-flex align-items-center justify-content-center'
          : 'page-link text-dark border rounded-0 d-flex align-items-center justify-content-center';
        link.style.cssText = pageNum === this.currentPage
          ? 'background-color: #f37a20; color: white; width: 32px; height: 32px; padding: 0; cursor: pointer;'
          : 'width: 32px; height: 32px; padding: 0; background: white; border: 1px solid #ddd; cursor: pointer;';
        link.textContent = pageNum;
        link.addEventListener('click', () => this.goToPage(pageNum));
        li.appendChild(link);
      }
      ul.appendChild(li);
    });

    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = 'page-item';
    if (this.currentPage === this.totalPages || this.totalPages === 0) {
      nextLi.classList.add('disabled');
    }
    const nextLink = document.createElement('button');
    nextLink.className = 'page-link text-dark border rounded-0 d-flex align-items-center justify-content-center';
    nextLink.style.cssText = 'width: 32px; height: 32px; padding: 0; background: white; border: 1px solid #ddd; cursor: pointer;';
    nextLink.innerHTML = '›';
    nextLink.disabled = this.currentPage === this.totalPages || this.totalPages === 0;
    nextLink.addEventListener('click', () => this.goToPage(this.currentPage + 1));
    nextLi.appendChild(nextLink);
    ul.appendChild(nextLi);

    nav.appendChild(ul);
    wrapper.appendChild(nav);
    container.appendChild(wrapper);
  }

  getPageNumbers() {
    const { maxVisiblePages } = this.config;
    const total = this.totalPages;
    const current = this.currentPage;
    const pages = [];

    if (total <= maxVisiblePages) {
      // Show all pages if total is less than max visible
      for (let i = 1; i <= total; i++) {
        pages.push(i);
      }
    } else {
      // Always show first page
      pages.push(1);

      let start = Math.max(2, current - Math.floor(maxVisiblePages / 2));
      let end = Math.min(total - 1, start + maxVisiblePages - 3);

      // Adjust if we're near the end
      if (end === total - 1) {
        start = Math.max(2, total - maxVisiblePages + 2);
      }

      if (start > 2) {
        pages.push('...');
      }

      for (let i = start; i <= end; i++) {
        pages.push(i);
      }

      if (end < total - 1) {
        pages.push('...');
      }

      // Always show last page
      if (total > 1) {
        pages.push(total);
      }
    }

    return pages;
  }

  goToPage(page) {
    if (page < 1 || page > this.totalPages || page === this.currentPage) {
      return;
    }
    this.currentPage = page;
    this.render();
    this.config.onPageChange(page, this.config.limit);
  }

  getCurrentPage() {
    return this.currentPage;
  }

  getLimit() {
    return this.config.limit;
  }
}

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
  module.exports = PaginationManager;
}
