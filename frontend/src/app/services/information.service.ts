import { Injectable, inject } from '@angular/core';
import { HttpClient} from '@angular/common/http';
import { environment } from '../../environments/environment';

const BASE_URL = `${environment.apiUrl}/api/information`;
@Injectable({
  providedIn: 'root'
})
export class InformationService {

  private http = inject(HttpClient)
  constructor() { }
  getInformation() {
  return this.http.get(BASE_URL);
  }
}
