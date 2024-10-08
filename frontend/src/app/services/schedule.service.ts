import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map,} from 'rxjs';
import { Artist } from './class';

// Service pour se connecter à l'API wordpress de la liste des artistes
const BASE_URL = 'https://jmcarre.go.yj.fr/nationsound/nationsoundbe/wp-json/wp/v2/programmations?page=1&per_page=100&acf_format=standard';
@Injectable({
  providedIn: 'root'
})
export class ScheduleService  {
  private http = inject(HttpClient)
  constructor() { }
  // Récupération des artistes et mappage des données
  getPosts(): Observable<Artist[]> {
    return this.http.get<Artist[]>(BASE_URL)
      .pipe(
        map((response: any[]) => {
          return response.map(item => ({
            id: item.id, 
            name: item.title.rendered, 
            description: item.acf.description_artistes, 
            type_musique: item.acf.type_musique,
            photo_artiste: item.acf.photo_artistes,
            date: item.acf.date_concert, 
            heure_debut: item.acf.heure_debut_concert, 
            heure_fin: item.acf.heure_fin_concert,
            type_evenement: item.acf.type_evenement,
            scene: item.acf.scene,
            lieu_rencontre: item.acf.lieu_rencontre
          }));
        })
      );
  }
}