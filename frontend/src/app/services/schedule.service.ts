import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map, shareReplay,} from 'rxjs';
import { Artist } from './class';
import { environment } from '../../environments/environment';

// Service pour se connecter à l'API wordpress de la liste des artistes
// Service to connect to the wordpress API of the list of artists
//const BASE_URL = 'https://jmcarre.go.yj.fr/nationsound/nationsoundbe/wp-json/wp/v2/programmations?page=1&per_page=100&acf_format=standard';
const BASE_URL = `${environment.apiUrl}/api/event`;
@Injectable({
  providedIn: 'root'
})
export class ScheduleService  {
  private http = inject(HttpClient);
  artists$!: Observable<Artist[]>; // Ajoutez cette ligne

  constructor() {
    this.artists$ = this.getPosts();
   }
  // Récupération des artistes et mappage des données
  // Getting artists and mapping the data
  getPosts(): Observable<Artist[]> {
    return this.http.get<Artist[]>(BASE_URL)
      .pipe(
        map((response: any[]) => {
          return response.map(item => ({
            id: item.id, 
            name: item.artist.name, 
            description: item.artist.description, 
            type_musique: item.artist.type_music,
            photo_artiste: `${environment.apiUrl}/uploads/artists/${item.artist.image}`,
            photo_thumbnail: `${environment.apiUrl}/uploads/artists/${item.artist.thumbnail}`,
            date: item.date.date, 
            heure_debut: item.heure_debut, 
            heure_fin: item.heure_fin,
            type_evenement: item.type.type,
            scene: item.eventLocation.locationName,
            publish: item.publish
          }));
        }),
        shareReplay(1)  // Ajoutez cette ligne pour éviter de recharger les données à chaque fois
      );
  }
}