// Assets/app.js
// import '@hotwired/turbo';
import { Application } from '@hotwired/stimulus';
import { registerControllers } from 'stimulus-vite-helpers';
import './styles/app.css';
import './styles/styles.scss';


// Turbo disabled - forms use standard submissions
// Turbo.start()

// Stimulus
const app = Application.start();
registerControllers(app, import.meta.glob('./controllers/**/*_controller.js'));


// Additional JavaScript files
import { sortableManager } from './modules/sortableManager.js';
import.meta.glob(['./images/**']);

// Initialize sortable management
sortableManager.setupEventListeners();


console.log('App loaded with Vite + Tailwind + DaisyUI');
