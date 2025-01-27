import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Observable, BehaviorSubject } from 'rxjs';
import { Artist } from '../services/class';
import { ArtistService } from '../services/artist.service';
import { CommonModule, Location } from '@angular/common';

@Component({
  selector: 'app-artist',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './artist.component.html',
  styleUrl: './artist.component.css'
})

export class ArtistComponent implements OnInit{
  artist$!: Observable<Artist | null>;
  loading$ = new BehaviorSubject<boolean>(true);
  error$ = new BehaviorSubject<boolean>(false);

  constructor(private route: ActivatedRoute,private artistService: ArtistService, private location: Location)
   {}

  ngOnInit(): void {
    // Récupération de l'identifiant de l'artiste
    // Getting the artist's ID
    const id = Number(this.route.snapshot.paramMap.get('id'));
    /*
    if (id) {
      this.artist$ = this.artistService.getArtist(id);
    } else {
      this.artist$ = new Observable<null>();
    }*/

    if (id) {
      this.loading$.next(true);
      this.error$.next(false);
      
      this.artistService.getArtist(id).subscribe({
        next: (artist) => {
          if (artist) {
            this.artist$ = new Observable(observer => {
              observer.next(artist);
              observer.complete();
            });
          } else {
            this.error$.next(true);
          }
          this.loading$.next(false);
        },
        error: () => {
          this.error$.next(true);
          this.loading$.next(false);
        }
      });
    } else {
      this.error$.next(true);
      this.loading$.next(false);
    }
  }

  goBack(): void {
    this.location.back();
  }
}
