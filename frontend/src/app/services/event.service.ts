import { Injectable, inject } from '@angular/core';
import { HttpClient} from '@angular/common/http';
import { Observable, map, shareReplay,} from 'rxjs';
import { Artist } from './class';
import { environment } from '../../environments/environment';

//const BASE_URL = 'http://127.0.0.1:8000/api/event'
const BASE_URL = `${environment.apiUrl}/api/event`;
@Injectable({
  providedIn: 'root'
})
export class EventService {

  private http = inject(HttpClient)
  artists$!: Observable<Artist[]>; 
  
  constructor() {
    this.artists$ = this.getEvent();
   }

   formatDate(dateString: string): string {
    const date = new Date(dateString);
    const day = date.getDate().toString().padStart(2, '0');
    const month = (date.getMonth()+1).toString().padStart(2, '0');
    const year = date.getFullYear();
    return  `${day}/${month}/${year}`;
  }

  formatTime(timeString: string): string {
    const time = new Date(timeString);
    const hours = time.getHours().toString().padStart(2, '0');
    const minutes = time.getMinutes().toString().padStart(2, '0');
    return `${hours}:${minutes}`;
  }


   getEvent(): Observable<Artist[]> {
    return this.http.get<Artist[]>(BASE_URL)
      .pipe(
        map((response: any[]) => {
          return response.map(event => ({
            id: event.id, 
            name: event.artist.name, 
            description: event.artist.description, 
            type_musique: event.artist.type_music,
            photo_artiste: `${environment.apiUrl}/uploads/artists/${event.artist.image}`,
            photo_thumbnail: `${environment.apiUrl}/uploads/artists/${event.artist.thumbnail}`,
            date: this.formatDate(event.date.date), 
            heure_debut: this.formatTime(event.heure_debut), 
            heure_fin: this.formatTime(event.heure_fin),
            type_evenement: event.type.type,
            scene: event.eventLocation.locationName,
            publish: event.publish
          }));
        }),
        shareReplay(1)  // Ajoutez cette ligne pour éviter de recharger les données à chaque fois
      );
  }
}