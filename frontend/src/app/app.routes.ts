import { Routes, RouterModule } from '@angular/router';
import { PartnerComponent } from './partner/partner.component';
import { AppComponent } from './app.component';
import { ProgrammationComponent } from './programmation/programmation.component';
import { NavBarInformationsComponent } from './nav-bar-informations/nav-bar-informations.component';
import { InformationComponent } from './information/information.component';
import { FaqComponent } from './faq/faq.component';
import { HomeComponent } from './home/home.component';
import { MapComponent } from './map/map.component';
import { ArtistComponent } from './artist/artist.component';
import { NewsSummaryComponent } from './news-summary/news-summary.component';
import { NewsDetailComponent } from './news-detail/news-detail.component';

export const routes: Routes = [
    {path:'accueil', component:HomeComponent},
    {path:'partenaires', component:PartnerComponent},
    {path:'carte', component:MapComponent},
    {path:'informations', component:NavBarInformationsComponent,    
    children: [
        { path: 'actualites', component: NewsSummaryComponent },
        { path: 'actualite/:id', component: NewsDetailComponent },
        { path: 'infos', component: InformationComponent },
        { path: 'faq', component: FaqComponent }, 
        { path: '', redirectTo: 'infos', pathMatch: 'full' }
    ],
    },
    {path:'programmation', component:ProgrammationComponent},
    {path:'artist/:id', component:ArtistComponent},
    {path:'', redirectTo: 'accueil', pathMatch: 'full'}
];