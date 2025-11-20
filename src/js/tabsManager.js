class BootstrapTabsManager {
  constructor(options) {
    this.options = options;
    this.currentTab = null;
    
    // Bind context for event handlers
    this.handleHashChange = this.handleHashChange.bind(this);
    this.handleTabShown = this.handleTabShown.bind(this);
    this.handleDOMContentLoaded = this.handleDOMContentLoaded.bind(this);
    
    this.init();
  }
  
  init() {
    // Add event listeners
    window.addEventListener('hashchange', this.handleHashChange);
    document.addEventListener('shown.bs.tab', this.handleTabShown);
    
    // Handle initial state after DOM load
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', this.handleDOMContentLoaded);
    } else {
      this.handleDOMContentLoaded();
    }
  }
  
  /**
   * Activates tab by key
   * @param {string} tabKey - Tab key from options
   */
  activateTab(tabKey) {
    const tabConfig = this.options.tabs[tabKey];
    if (!tabConfig) {
      console.warn(`Tab configuration not found for key: ${tabKey}`);
      return;
    }
    
    const targetElement = document.getElementById(tabConfig.tab);
    if (targetElement) {
      const bsTab = new bootstrap.Tab(targetElement);
      bsTab.show();
      this.currentTab = tabKey;
    } else {
      console.warn(`Tab element not found: ${tabConfig.tab}`);
    }
  }
  
  /**
   * Updates URL hash and page title
   * @param {string} tabKey - Tab key
   */
  updateWindowState(tabKey) {
    const tabConfig = this.options.tabs[tabKey];
    if (!tabConfig) return;
    
    // Update hash
    if (tabConfig.hash) {
      window.location.hash = `#${tabConfig.hash}`;
    }
    
    // Update title
    if (tabConfig.title) {
      document.title = `${this.options.titleBase} — ${tabConfig.title}`;
    }
    
    this.currentTab = tabKey;
  }
  
  /**
   * URL hash change handler
   */
  handleHashChange() {
    const hash = window.location.hash.substring(1);
    
    // Find tab by hash
    for (const [tabKey, tabConfig] of Object.entries(this.options.tabs)) {
      if (tabConfig.hash === hash) {
        this.activateTab(tabKey);
        return;
      }
    }
    
    // If hash not found, activate default tab
    if (this.options.defaultTab) {
      this.activateTab(this.options.defaultTab);
    }
  }
  
  /**
   * Bootstrap tab show event handler
   * @param {Event} event - shown.bs.tab event
   */
  handleTabShown(event) {
    const activeTabId = event.target.id;
    
    // Find tab by element ID
    for (const [tabKey, tabConfig] of Object.entries(this.options.tabs)) {
      if (tabConfig.tab === activeTabId) {
        this.updateWindowState(tabKey);
        return;
      }
    }
  }
  
  /**
   * DOM load handler
   */
  handleDOMContentLoaded() {
    this.handleHashChange();
  }
  
  /**
   * Gets current active tab
   * @returns {string|null} Current tab key
   */
  getCurrentTab() {
    return this.currentTab;
  }
  
  /**
   * Destroys class instance by removing event handlers
   */
  destroy() {
    window.removeEventListener('hashchange', this.handleHashChange);
    document.removeEventListener('shown.bs.tab', this.handleTabShown);
    document.removeEventListener('DOMContentLoaded', this.handleDOMContentLoaded);
  }
}
