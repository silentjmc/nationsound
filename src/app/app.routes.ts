import { Routes, RouterModule } from '@angular/router';
import { PartnerComponent } from './partner/partner.component';
import { AppComponent } from './app.component';
import { InformationComponent } from './information/information.component';

export const routes: Routes = [
    {path:'partenaires', component:PartnerComponent},
    {path:'informations', component:InformationComponent}
];
