import { Pipe, PipeTransform } from '@angular/core';

// Pipe pour filtrer les partenaires par type
@Pipe({
  name: 'partnerFilter',
  standalone: true
})
export class PartnerFilterPipe implements PipeTransform {

  transform(partners: any[], type: string): any[] {
    const filteredPosts = partners.filter(partner => partner.typePartenaire === type);
    return filteredPosts;
  }

}
