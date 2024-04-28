// import external dependencies
import 'jquery';
import 'slick-carousel';
import 'custom-event-polyfill';
import './newBulkOrdersTable';

// import the needed font awesome functionality
import { library, dom, config } from '@fortawesome/fontawesome-svg-core';
// import any icons you need here
import {
  faShoppingCart,
  faUserCircle,
  faChevronCircleDown,
  faCaretRight,
  faChevronDown,
  faChevronLeft,
  faChevronRight,
  faSearch,
  faBars,
  faTimes,
  faPrint,
} from '@fortawesome/pro-light-svg-icons';
import {
  faChevronCircleDown as fasChevronCircleDown,
  faSort,
  faCaretRight as fasCaretRight,
  faCaretLeft,
  faSpinner,
  faPen,
  faCheck,
  faPlusCircle,
  faStar,
  faTimes as fasTimes,
} from '@fortawesome/pro-solid-svg-icons';
import {
  faFacebook,
  faTwitter,
  faInstagram,
} from '@fortawesome/free-brands-svg-icons';

// import local dependencies
import Router from './util/Router';
import common from './routes/common';
import home from './routes/home';
import singleProduct from './routes/singleProduct';
import theIresqDifference from './routes/theIresqDifference';
import servicesDevices from './routes/servicesDevices';
import blog from './routes/blog';
import woocommerceDevices from './routes/woocommerceDevices';
import checkout from './routes/checkout';
import cart from './routes/cart';
import postTypeArchiveProduct from './routes/postTypeArchiveProduct';
import myAccount from './routes/myAccount';
config.searchPseudoElements = true;
// add the imported icons to the library
library.add(
  faShoppingCart,
  faUserCircle,
  faFacebook,
  faTwitter,
  faInstagram,
  faSearch,
  faSpinner,
  faPrint,
  faCheck,
  faChevronDown,
  faChevronCircleDown,
  faCaretRight,
  faSort,
  faStar,
  fasChevronCircleDown,
  faChevronLeft,
  faChevronRight,
  faBars,
  faTimes,
  faPlusCircle,
  fasCaretRight,
  faCaretLeft,
  faPen,
  fasTimes,
);

// tell FontAwesome to watch the DOM and add the SVGs when it detcts icon markup
dom.watch();

/** Populate Router instance with DOM routes */
const routes = new Router({
  // All pages
  common,
  // Home page
  home,
  // Single product page
  singleProduct,
  // The iResQ Difference page
  theIresqDifference,
  // Services and Devices page
  servicesDevices,
  // blog page
  blog,
  // devices page
  woocommerceDevices,
  // checkout page
  checkout,
  // cart page
  cart,
  // Shop page
  postTypeArchiveProduct,
  // My account
  myAccount
});

// Load Events
jQuery(document).ready(() => routes.loadEvents());
