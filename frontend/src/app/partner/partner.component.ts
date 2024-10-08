import { Component, inject } from '@angular/core';
import { PartnerswpService } from '../services/partnerswp.service';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { PartnerFilterPipe } from '../pipe/partner-filter.pipe';

@Component({
  selector: 'app-partner',
  standalone: true,
  imports: [CommonModule, PartnerFilterPipe],
  templateUrl: './partner.component.html',
  styleUrl: './partner.component.css'
})
export class PartnerComponent {
  http = inject(HttpClient);
  partners: any = [];
  mappedPartners: any = [];
  private partnersService = inject(PartnerswpService);

  ngOnInit(): void {
    this.loadPartners();
  }

  // function to load all partners and map result with only necessary property
  loadPartners() {
    this.partnersService.getPosts().subscribe({
      next: (partners : any) => {
        this.mappedPartners = partners.map((partner: any) => {
          return {
            id: partner.id,
            title: partner.title.rendered,  // title for alt img
            urlPartenaire: partner.acf.url_partenaire,
            logoPartenaire: partner.acf.logo_partenaire,  // src of img
            typePartenaire:partner.acf.type_partenaire
          }
        });  
      },
      error : (error) => console.log('Error fetching partners', error)

    });
  }

}
