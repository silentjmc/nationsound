import { Component } from '@angular/core';
import { CommonModule} from '@angular/common';
import { Meta, Title } from '@angular/platform-browser';


@Component({
  selector: 'app-information',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './information.component.html',
  styleUrl: './information.component.css', 
})

export class InformationComponent {
  constructor(private meta: Meta, private title: Title) {
    title.setTitle("Nation Sound Festival 2024 - Informations Pratiques");

    meta.addTags([
      { name: 'description', content: 'Toutes les informations pratiques sur le Nation Sound Festival 2024. Horaires, accès, billets, et services sur place. Préparez votre venue et profitez pleinement du festival !' }
    ]);
  }

}
