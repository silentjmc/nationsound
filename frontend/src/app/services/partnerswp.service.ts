import { Injectable, inject } from '@angular/core';
import { HttpClient} from '@angular/common/http';
import { environment } from '../../environments/environment';
import { map, Observable } from 'rxjs';
import { Partner } from './class';

const BASE_URL = `${environment.apiUrl}/api/partners`;
@Injectable({
  providedIn: 'root'
})
export class PartnerswpService {
  private http = inject(HttpClient)
  partners$!: Observable<Partner[]>; 
  constructor() { }

  getPartners(): Observable<Partner[]> {
    return this.http.get<Partner[]>(BASE_URL).pipe(
      map((partners: Partner[]) => {
        return partners.map(partner => ({
          ...partner,
          image: `${environment.apiUrl}/uploads/partners/${partner.image}`
        }));
      })
    );
  }
}