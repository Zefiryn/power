import { startStimulusApp } from '@symfony/stimulus-bridge';
import ReadingRowController from './controllers/reading-row_controller';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
app.register('reading-row', ReadingRowController);
