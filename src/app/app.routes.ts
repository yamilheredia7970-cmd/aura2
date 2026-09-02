import {Component} from '@angular/core';
import {Routes} from '@angular/router';

// Empty routed component: these routes exist only so legal pages have a
// real, linkable URL. The actual content renders as an overlay from the
// root App component (see app.ts `activeLegalPage`), not via this outlet.
@Component({selector: 'app-legal-outlet', standalone: true, template: ''})
class LegalOutletComponent {}

export const routes: Routes = [
  {path: '', component: LegalOutletComponent},
  {path: 'terms-and-conditions', component: LegalOutletComponent},
  {path: 'privacy-policy', component: LegalOutletComponent},
  {path: 'returns-and-exchanges', component: LegalOutletComponent},
  {path: 'right-of-withdrawal', component: LegalOutletComponent},
];
