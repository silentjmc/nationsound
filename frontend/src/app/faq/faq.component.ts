import { CommonModule } from '@angular/common';
import { Component, Inject, PLATFORM_ID, OnDestroy, inject  } from '@angular/core';
import { Meta, Title } from '@angular/platform-browser';
import { FaqService } from '../services/faq.service';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-faq',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './faq.component.html',
  styleUrl: './faq.component.css'
})

export class FaqComponent implements   OnDestroy  {
  private faqService = inject(FaqService);
  http = inject(HttpClient);
  faqs: any = [];
  mappedFaq: any = [];

  // Information for SEO
  constructor(private meta: Meta, private title: Title, @Inject(PLATFORM_ID) private platformId: Object) {
    title.setTitle("Nation Sound Festival 2024 - FAQ");
    meta.addTags([
      { name: 'description', content: "Vous avez des questions sur le Nation Sound Festival 2024 ? Consultez notre FAQ pour obtenir des réponses sur les billets, l'hébergement, les consignes de sécurité et bien plus encore." }
    ]);
  }

  ngOnInit(): void {
    this.loadFaq();
  }

  ngOnDestroy(): void {
    // Remove the meta tag when the component is destroyed
    this.meta.removeTag("name='description'");
  }

  loadFaq() {
    this.faqService.getFaq().subscribe({
      next: (faqs : any) => {
        this.mappedFaq = faqs.map((faq: any) => {
          return {
            id: faq.id,
            question: faq.question,
            reponse: faq.reponse,
          }
        });
        //console.log('Faq', this.mappedFaq);
      },
      error : (error) => console.log('Error fetching faq', error)
    });
  } 
  
  toggleAccordion(index: number): void {
    const content = document.getElementById(`content-${index}`);
    const icon = document.getElementById(`icon-${index}`);

    if (!content || !icon) {
      console.error(`Element not found: content-${index} or icon-${index}`);
      return;
    }

    // SVG for Minus icon
    const minusSVG = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-5 h-4">
        <path d="M3.75 7.25a.75.75 0 0 0 0 1.5h8.5a.75.75 0 0 0 0-1.5h-8.5Z" />
      </svg>
    `;

    // SVG for Plus icon
    const plusSVG = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-5 h-5">
        <path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
      </svg>
    `;

    // Toggle the content's max-height for smooth opening and closing
    if (content.style.maxHeight && content.style.maxHeight !== '0px') {
      content.style.maxHeight = '0';
      icon.innerHTML = plusSVG;
    } else {
      content.style.maxHeight = content.scrollHeight + 'px';
      icon.innerHTML = minusSVG;
    }
  }
}