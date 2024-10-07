import { isPlatformBrowser } from '@angular/common';
import { AfterViewInit, Component, Inject, PLATFORM_ID, OnDestroy  } from '@angular/core';
import { Meta, Title } from '@angular/platform-browser';
import { initFlowbite } from 'flowbite';

@Component({
  selector: 'app-faq',
  standalone: true,
  imports: [],
  templateUrl: './faq.component.html',
  styleUrl: './faq.component.css'
})
export class FaqComponent implements AfterViewInit, OnDestroy  {
  // Information pour SEO
  // Information for SEO
  constructor(private meta: Meta, private title: Title, @Inject(PLATFORM_ID) private platformId: Object) {
    title.setTitle("Nation Sound Festival 2024 - FAQ");
    meta.addTags([
      { name: 'description', content: "Vous avez des questions sur le Nation Sound Festival 2024 ? Consultez notre FAQ pour obtenir des réponses sur les billets, l'hébergement, les consignes de sécurité et bien plus encore." }
    ]);
  }

  ngAfterViewInit(): void {
    // Si le navigateur est disponible, on initialise Flowbite
    // If the browser is available, we initialize Flowbite
    if (isPlatformBrowser(this.platformId)) {
    initFlowbite();
    }
  }

  ngOnDestroy(): void {
    // Supprimer la balise meta lorsque le composant est détruit
    // Remove the meta tag when the component is destroyed
    this.meta.removeTag("name='description'");
  }
}

