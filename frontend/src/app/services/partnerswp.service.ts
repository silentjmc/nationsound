import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import { Observable } from 'rxjs';

// Service for connect to API of the list of partners 
const BASE_URL = 'https://jmcarre.go.yj.fr/nationsound/nationsoundbe/wp-json/wp/v2/partenaires?per_page=100&acf_format=standard';


@Injectable({
  providedIn: 'root'
})
export class PartnerswpService {
  private http = inject(HttpClient)
  constructor() { }

  getPosts() {
    return this.http.get(BASE_URL);
  }
}