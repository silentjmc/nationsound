import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { EventService } from '../services/event.service';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { Artist } from '../services/class';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-artist',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './artist.component.html',
  styleUrl: './artist.component.css'
})

export class ArtistComponent implements OnInit{
  artist$!: Observable<Artist | null>;

  constructor(private route: ActivatedRoute,private scheduleService: EventService)
   {}

  ngOnInit(): void {
    // Récupération de l'identifiant de l'artiste
    // Getting the artist's ID
    const id = Number(this.route.snapshot.paramMap.get('id'));
    // Si l'identifiant existe, on récupère l'artiste correspondant sinon on renvoie null
    // If the ID exists, we get the corresponding artist otherwise we return null
    if (id) {
    //  Récupération des données artistes depuis le service selon l'identifiant
    //  Get artist data from the service according to the ID
      this.artist$ = this.scheduleService.artists$.pipe(
        map(artists => artists.find(artist => artist.id === id) ?? null)
      );
    }
  }
}
