import { Component, OnDestroy, inject } from '@angular/core';
import { PartnerswpService } from '../services/partnerswp.service';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { PartnerFilterPipe } from '../pipe/partner-filter.pipe';
import { Meta, Title } from '@angular/platform-browser';

@Component({
  selector: 'app-partner',
  standalone: true,
  imports: [CommonModule, PartnerFilterPipe],
  templateUrl: './partner.component.html',
  styleUrl: './partner.component.css'
})
export class PartnerComponent implements OnDestroy{
  // Information pour SEO
  // Information for SEO
  constructor(private meta: Meta, private title: Title) {
    title.setTitle("Partenaires du Nation Sound Festival 2024");
    meta.addTags([
      { name: 'description', content: 'Découvrez nos partenaires qui rendent le Nation Sound Festival 2024 possible. Merci à eux pour leur soutien et leur engagement à faire de cet événement un succès !' }
    ]);
  }

  http = inject(HttpClient);
  partners: any = [];
  mappedPartners: any = [];
  private partnersService = inject(PartnerswpService);

  // fonction pour charger les partenaires au chargement de la page 
  // function to load partners when the page is loaded
  ngOnInit(): void {
    this.loadPartners();
  }

  ngOnDestroy(): void {
    // Supprimer la balise meta lorsque le composant est détruit
    // Remove the meta tag when the component is destroyed
    this.meta.removeTag("name='description'");
  }

  // fonction pour charger les partenaires et mapper les données
  // function to load partners and map the data
  loadPartners() {
    this.partnersService.getPosts().subscribe({
      next: (partners : any) => {
        this.mappedPartners = partners.map((partner: any) => {
          return {
            id: partner.id,
            title: partner.title.rendered,
            urlPartenaire: partner.acf.url_partenaire,
            logoPartenaire: partner.acf.logo_partenaire,
            typePartenaire:partner.acf.type_partenaire
          }
        });  
      },
      error : (error) => console.log('Error fetching partners', error)
    });
  }
}
