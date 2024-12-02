import { Component, OnDestroy, inject } from '@angular/core';
import { CommonModule} from '@angular/common';
import { Meta, Title } from '@angular/platform-browser';
import { InformationService } from '../services/information.service';
import { HttpClient } from '@angular/common/http';


@Component({
  selector: 'app-information',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './information.component.html',
  styleUrl: './information.component.css', 
})

export class InformationComponent implements OnDestroy {
  private informationService = inject(InformationService);
  http = inject(HttpClient);
  informationsSection: any = [];
  mappedInformationsSection: any = [];  

  // Information for SEO
  constructor(private meta: Meta, private title: Title) {
    title.setTitle("Nation Sound Festival 2024 - Informations Pratiques");
    meta.addTags([
      { name: 'description', content: 'Toutes les informations pratiques sur le Nation Sound Festival 2024. Horaires, accès, billets, et services sur place. Préparez votre venue et profitez pleinement du festival !' }
    ]);
  }

  ngOnInit(): void {
    this.loadInformation();
  }

  ngOnDestroy(): void {
    // Remove the meta tag when the component is destroyed
    this.meta.removeTag("name='description'");
  }

  loadInformation() {
    this.informationService.getInformation().subscribe({
        next: (informationsSection: any) => {
            //console.log('Raw Information Data:', informationsSection);

            this.mappedInformationsSection = informationsSection.map((informationSection: any) => {
                const mappedSection = {
                    id: informationSection.id,
                    section: informationSection.section,
                    titre: informationSection.title,
                    description: informationSection.description,
                    position: informationSection.position,
                    information: informationSection.information.map((info: any) => ({
                        id: info.id,
                        titre: info.titre,
                        description: info.description,
                        publish: info.publish,
                        position: info.position
                    }))
                };
                //console.log('Mapped Section:', mappedSection);
                return mappedSection;
            });

           //console.log('Mapped Information:', this.mappedInformationsSection);
        },
        error: (error) => console.log('Error fetching information', error)
    });
  }
}
