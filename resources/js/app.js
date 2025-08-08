// Import bootstrap.js which handles axios configuration
import './bootstrap';

// Pull in your custom SCSS (which imports Bootstrap and your overrides)
import '../sass/app.scss';

// Bootstrap's JS (dropdowns, collapse, etc)  
import * as bootstrap from 'bootstrap';

// Make Bootstrap available globally
window.bootstrap = bootstrap;

// Import feature-specific JavaScript modules
import './clinic-approvals.js'; // Import clinic approvals functionality
import './secretary-services.js'; // Import secretary services functionality
