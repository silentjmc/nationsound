import { Injectable, inject } from '@angular/core';
import { HttpClient} from '@angular/common/http';
import { Observable, map, shareReplay} from 'rxjs';
import { Program } from './class';
import { environment } from '../../environments/environment';

const BASE_URL = `${environment.apiUrl}/api/event`;
@Injectable({
  providedIn: 'root'
})
export class EventService {
  private http = inject(HttpClient)
  programs$!: Observable<Program[]>; 
  
  constructor() {
    this.programs$ = this.getEvent();
   }

   formatDate(dateString: string): string {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth()+1).toString().padStart(2, '0');
    const year = date.getFullYear();
    return  `${day}/${month}/${year}`;
  }

  formatTime(timeString: string): string {
    // The hours and minutes are extracted directly from the ISO string
    // Expected format: "1970-01-01T11:11:00+01:00"
    const timeMatch = timeString.match(/T(\d{2}):(\d{2})/);
    if (timeMatch) {
      const [, hours, minutes] = timeMatch;
      return `${hours}:${minutes}`;
    }
    return '00:00';
  }

  getEvent(): Observable<Program[]> {
    return this.http.get<Program[]>(BASE_URL).pipe(
      map((response: Program[]) => {
        return response.map(event => ({
          ...event,
          artist: {
            ...event.artist,
            image: `${environment.apiUrl}/uploads/artists/${event.artist.imageArtist}`,
            thumbnail: `${environment.apiUrl}/uploads/artists/${event.artist.thumbnail}`
          }
        }));
      }),
      shareReplay(1)
    );
  }
}