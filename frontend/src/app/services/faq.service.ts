import { Injectable, inject } from '@angular/core';
import { HttpClient} from '@angular/common/http';

const BASE_URL = 'http://127.0.0.1:8000/api/faq'
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