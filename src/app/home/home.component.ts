import { Component, OnDestroy } from '@angular/core';
import { MapComponent } from '../map/map.component';
import { Meta, Title } from '@angular/platform-browser';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [MapComponent],
  templateUrl: './home.component.html',
  styleUrl: './home.component.css'
})
export class HomeComponent implements OnDestroy{
    // Information pour SEO
    // Information for SEO
  constructor(private meta: Meta, private title: Title) {
    title.setTitle("Nation Sound Festival 2024 - Le Rendez-vous Musical de l'Été");
    meta.addTags([
      { name: 'description', content: 'Découvrez le festival Nation Sound 2024 ! Cinq scènes dédiées à la musique métal, rock, rap/urban, world et électro vous attendent pour trois jours de concerts inoubliables. Rejoignez-nous pour une expérience musicale unique et festive !' }
    ]);
  }

  ngOnDestroy(): void {
    // Supprimer la balise meta lorsque le composant est détruit
    // Remove the meta tag when the component is destroyed
    this.meta.removeTag("name='description'");
  }
}
