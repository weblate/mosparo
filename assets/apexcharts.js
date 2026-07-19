/**
 * This file is only needed to store the apexcharts.css file into a separated file.
 * The source code of apexcharts checks for an HTML element with the id "apexcharts-css". If there is no
 * such element, apexcharts includes the styles directly in the JavaScript codes, which invalidates the
 * CSP header.
 */
import 'apexcharts/dist/apexcharts.css';
