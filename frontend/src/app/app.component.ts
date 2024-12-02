import { Component, Inject, OnInit, PLATFORM_ID } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { HeaderComponent } from './header/header.component';
import { initFlowbite } from 'flowbite';
import { CommonModule, isPlatformBrowser } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { InformationComponent } from './information/information.component';
import { PartnerFilterPipe } from './pipe/partner-filter.pipe';
import { SortPipe } from './pipe/sort-by.pipe';
import { FooterComponent } from './footer/footer.component';
import { MapComponent } from './map/map.component';
import { AlertNewsComponent } from './alert-news/alert-news.component';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, CommonModule, HeaderComponent, InformationComponent, PartnerFilterPipe, FooterComponent, MapComponent, AlertNewsComponent],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  title = 'nationsound';

  constructor(
    @Inject(PLATFORM_ID) private platformId: Object,
  ) {}
}
