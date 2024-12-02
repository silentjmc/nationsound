import { Component, OnDestroy, inject } from '@angular/core';
import { PartnerswpService } from '../services/partnerswp.service';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { PartnerFilterPipe } from '../pipe/partner-filter.pipe';
import { Meta, Title } from '@angular/platform-browser';
import { environment } from '../../environments/environment';

@Component({
  selector: 'app-partner',
  standalone: true,
  imports: [CommonModule, PartnerFilterPipe],
  templateUrl: './partner.component.html',
  styleUrl: './partner.component.css'
})
export class PartnerComponent implements OnDestroy{
  // Information for SEO
  constructor(private meta: Meta, private title: Title) {
    title.setTitle("Partenaires du Nation Sound Festival 2024");
    meta.addTags([
      { name: 'description', content: 'Découvrez nos partenaires qui rendent le Nation Sound Festival 2024 possible. Merci à eux pour leur soutien et leur engagement à faire de cet événement un succès !' }
    ]);
  }

  http = inject(HttpClient);
  partners: any = [];
  uniquePartnerTypes: string[] = [];
  mappedPartners: any = [];
  private partnersService = inject(PartnerswpService);

  // function to load partners when the page is loaded
  ngOnInit(): void {
    this.loadPartners();
  }

  ngOnDestroy(): void {
    // Remove the meta tag when the component is destroyed
    this.meta.removeTag("name='description'");
  }

  // function to load partners and map the data
  loadPartners() {
    this.partnersService.getPosts().subscribe({
      next: (partners : any) => {
        this.mappedPartners = partners.map((partner: any) => {
          return {
            id: partner.id,
            title: partner.name,
            urlPartenaire: partner.url,
            logoPartenaire: `${environment.apiUrl}/uploads/partners/${partner.image}`,
            typePartenaire:partner.type.type,
          }
        });
        this.uniquePartnerTypes = [...new Set(this.mappedPartners.map((partner: any) => partner.typePartenaire))] as string[];
        //console.log('Partners', this.mappedPartners);
        //console.log('Partner Types', this.uniquePartnerTypes);  
      },
      error : (error) => console.log('Error fetching partners', error)
    });
  }
}
