import { Routes, RouterModule } from '@angular/router';
import { PartnerComponent } from './partner/partner.component';
import { AppComponent } from './app.component';
import { ProgrammationComponent } from './programmation/programmation.component';
import { NavBarInformationsComponent } from './nav-bar-informations/nav-bar-informations.component';
import { InformationComponent } from './information/information.component';
import { FaqComponent } from './faq/faq.component';

export const routes: Routes = [
    {path:'partenaires', component:PartnerComponent},
    {path:'informations', component:NavBarInformationsComponent,
    children: [
        { path: 'infos', component: InformationComponent },
        { path: 'faq', component: FaqComponent }, 
        { path: '', redirectTo: 'infos', pathMatch: 'full' }
    ],
    },
    {path:'programmation', component:ProgrammationComponent}
];
