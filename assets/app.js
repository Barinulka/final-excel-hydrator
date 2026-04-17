import './stimulus_bootstrap.js';
import { flushPendingToast } from './utils/toast.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/base.css';
import './styles/app.css';

document.addEventListener('DOMContentLoaded', flushPendingToast);
document.addEventListener('turbo:load', flushPendingToast);
