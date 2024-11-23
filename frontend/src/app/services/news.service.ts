import { Injectable, inject } from '@angular/core';
import { HttpClient} from '@angular/common/http';
import { environment } from '../../environments/environment';
import { Observable } from 'rxjs';
import { News } from './class';

const BASE_URL = `${environment.apiUrl}/api/news`;
@Injectable({
  providedIn: 'root'
})
export class NewsService {
  private http = inject(HttpClient)
  news$!: Observable<News[]>;
  constructor() { }

  getAllNews(): Observable<News[]> {
    return this.http.get<News[]>(BASE_URL);
  }

  getNewsById(id: number): Observable<News> {
    return this.http.get<News>(`${BASE_URL}/${id}`);
  }
}