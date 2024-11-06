import { Injectable, inject } from '@angular/core';
import { HttpClient} from '@angular/common/http';

const BASE_URL = 'http://127.0.0.1:8000/api/information'
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
