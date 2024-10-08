import { Injectable, inject } from '@angular/core';
import { HttpClient} from '@angular/common/http';

// Service pour se connecter à l'API wordpress de la liste des partenaires
const BASE_URL = 'https://jmcarre.go.yj.fr/nationsound/nationsoundbe/wp-json/wp/v2/partenaires?per_page=100&acf_format=standard';

@Injectable({
  providedIn: 'root'
})
export class PartnerswpService {
  private http = inject(HttpClient)
  constructor() { }
  // Récupération des partenaires
  getPosts() {
    return this.http.get(BASE_URL);
  }
}