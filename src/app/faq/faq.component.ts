import { isPlatformBrowser } from '@angular/common';
import { AfterViewInit, Component, Inject, PLATFORM_ID } from '@angular/core';
import { Meta, Title } from '@angular/platform-browser';
import { initFlowbite } from 'flowbite';

@Component({
  selector: 'app-faq',
  standalone: true,
  imports: [],
  templateUrl: './faq.component.html',
  styleUrl: './faq.component.css'
})
export class FaqComponent implements AfterViewInit {
  // Information pour SEO
  constructor(private meta: Meta, private title: Title, @Inject(PLATFORM_ID) private platformId: Object) {
    title.setTitle("Nation Sound Festival 2024 - FAQ");
    meta.addTags([
      { name: 'description', content: "Vous avez des questions sur le Nation Sound Festival 2024 ? Consultez notre FAQ pour obtenir des réponses sur les billets, l'hébergement, les consignes de sécurité et bien plus encore." }
    ]);
  }

  ngAfterViewInit(): void {
    if (isPlatformBrowser(this.platformId)) {
    // Initialisation de Flowbite
    initFlowbite();
    }
  }
}
