import { Component, OnDestroy, inject } from '@angular/core';
import { PartnerswpService } from '../services/partnerswp.service';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { PartnerFilterPipe } from '../pipe/partner-filter.pipe';
import { Meta, Title } from '@angular/platform-browser';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { Partner } from '../services/class';

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
  partners$!: Observable<Partner[]>
  uniquePartnerTypes: string[] = [];
  mappedPartners: Partner[] = [];
  private partnersService = inject(PartnerswpService);
  loading$ = new BehaviorSubject<boolean>(true);
  error$ = new BehaviorSubject<boolean>(false);

  private retrievePartners(): void {
    this.partnersService.getPartners().subscribe({
      next: (partners: Partner[]) => {
        if (partners && partners.length > 0) {
          this.partners$ = of(partners);
          this.mappedPartners = partners; // Directly assign the fetched partners
          this.uniquePartnerTypes = [...new Set(this.mappedPartners.map((partner: Partner) => partner.typePartner.titlePartnerType))] as string[];
          this.loading$.next(false);
        } else {
          this.error$.next(true);
          this.loading$.next(false);
        }
      },
      error: () => {
        this.error$.next(true);
        this.loading$.next(false);
      }
    });
  }
      
  ngOnInit(): void {
    this.loading$.next(true);
    this.error$.next(false);
    this.retrievePartners();
  }

  ngOnDestroy(): void {
    // Remove the meta tag when the component is destroyed
    this.meta.removeTag("name='description'");
  }
}