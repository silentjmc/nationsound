import { Injectable, inject } from '@angular/core';
import { HttpClient} from '@angular/common/http';
import { environment } from '../../environments/environment';

const BASE_URL = `${environment.apiUrl}/api/partners`;
@Injectable({
  providedIn: 'root'
})
export class PartnerswpService {
  private http = inject(HttpClient)
  constructor() { }
  // Getting partners
  getPosts() {
    return this.http.get(BASE_URL);
  }
}