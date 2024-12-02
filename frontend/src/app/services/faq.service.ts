import { Injectable, inject } from '@angular/core';
import { HttpClient} from '@angular/common/http';
import { environment } from '../../environments/environment';

const BASE_URL = `${environment.apiUrl}/api/faq`;
@Injectable({
  providedIn: 'root'
})
export class FaqService {

  private http = inject(HttpClient)
  constructor() { }
  getFaq() {
    return this.http.get(BASE_URL);
  }
}