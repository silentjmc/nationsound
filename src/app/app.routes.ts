import { Routes, RouterModule } from '@angular/router';
import { PartnerComponent } from './partner/partner.component';
import { AppComponent } from './app.component';
import { InformationComponent } from './information/information.component';
import { ProgrammationComponent } from './programmation/programmation.component';

export const routes: Routes = [
    {path:'partenaires', component:PartnerComponent},
    {path:'informations', component:InformationComponent},
    {path:'programmation', component:ProgrammationComponent}
];
