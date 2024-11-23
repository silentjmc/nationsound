import { Component } from '@angular/core';
import { RouterLink, RouterLinkActive, RouterModule } from '@angular/router';
import { InformationComponent } from '../information/information.component';
import { FaqComponent } from '../faq/faq.component';
import { NewsSummaryComponent } from '../news-summary/news-summary.component';

@Component({
  selector: 'app-nav-bar-informations',
  standalone: true,
  imports: [RouterLink, RouterLinkActive, RouterModule, InformationComponent, FaqComponent, NewsSummaryComponent],
  templateUrl: './nav-bar-informations.component.html',
  styleUrl: './nav-bar-informations.component.css'
})
export class NavBarInformationsComponent {

}
