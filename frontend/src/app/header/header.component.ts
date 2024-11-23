import { isPlatformBrowser } from '@angular/common';
import { Component, Inject, OnInit, PLATFORM_ID } from '@angular/core';
import { initFlowbite } from 'flowbite';
import { RouterLink, RouterLinkActive, RouterModule } from '@angular/router';

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [RouterLink, RouterLinkActive, RouterModule],
  templateUrl: './header.component.html',
  styleUrl: './header.component.css'
})
export class HeaderComponent implements OnInit {
  // Injecte PLATFORM_ID pour vérifier si on est dans un navigateur ou pas
  // Inject PLATFORM_ID to check if we are in a browser or not
  constructor(  
    @Inject(PLATFORM_ID) private platformId: Object,
  ) {}

  *ngOnInit() {
    if (isPlatformBrowser(this.platformId)) initFlowbite();
  }

}
