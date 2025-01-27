import { Component, CUSTOM_ELEMENTS_SCHEMA,OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ArtistService } from '../services/artist.service';
import { BehaviorSubject, catchError, EMPTY, Observable } from 'rxjs';
import { Artist } from '../services/class';
import { RouterLink, RouterModule } from '@angular/router';


@Component({
  selector: 'app-carousel',
  standalone: true,
  imports: [CommonModule,RouterModule,RouterLink],
  templateUrl: './carousel.component.html',
  styleUrl: './carousel.component.css',
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})

export class CarouselComponent implements OnInit  {
  artists$!: Observable<Artist[]>;
  loading$ = new BehaviorSubject<boolean>(true);
  error$ = new BehaviorSubject<boolean>(false);
  constructor(private artistService: ArtistService) {
  }

  ngOnInit(): void {
    this.loading$.next(true);
    this.error$.next(false);
    //  Get artist data from the service
    //this.artists$ = this.artistService.getAllArtists();
    this.artists$ = this.artistService.getAllArtists().pipe(
      catchError(() => {
        this.error$.next(true);
        this.loading$.next(false);
        return EMPTY; 
      })
    );
    this.artists$.subscribe(() => {
      this.loading$.next(false); 
    });
  }
}
