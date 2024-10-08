import { Pipe, PipeTransform } from '@angular/core';

// Pipe to filter partners by type
@Pipe({
  name: 'partnerFilter',
  standalone: true
})
export class PartnerFilterPipe implements PipeTransform {

  // return partners filtered by type : Instution, Entreprise, Média
  transform(partners: any[], type: string): any[] {
    const filteredPosts = partners.filter(partner => partner.typePartenaire === type);
    return filteredPosts;
  }

}
