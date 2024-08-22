import { Component, CUSTOM_ELEMENTS_SCHEMA,OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ScheduleService } from '../services/schedule.service';
import { Observable } from 'rxjs';
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
  constructor(private scheduleService: ScheduleService) {
  }

  ngOnInit(): void {
    //  Récupération des données artistes depuis le service
    //  Get artist data from the service
    this.artists$ = this.scheduleService.artists$;
  }
}