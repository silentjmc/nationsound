import { TestBed, ComponentFixture } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { PLATFORM_ID } from '@angular/core';
import { of } from 'rxjs';
import { ActivatedRoute } from '@angular/router';
import { HeaderComponent } from './header.component';


describe('HeaderComponent', () => {
  // Déclaration des variables qui seront utilisées dans les tests
  // Declaration of the variables that will be used in the tests
  let component: HeaderComponent;
  let fixture: ComponentFixture<HeaderComponent>;

  // Avant chaque test, nous configurons le module de test et créons une instance du composant à tester
  // Before each test, we set up the test module and create an instance of the component to test
  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ HeaderComponent],
      providers: [
        { provide: PLATFORM_ID, useValue: 'browser' },
        // simulation du service ActivatedRoute
        // simulation of the ActivatedRoute service
        { provide: ActivatedRoute, useValue: { params: of({}) } } 
      ]
    }).compileComponents();
  });

  // Avant chaque test, nous créons une nouvelle instance du composant et du service
  // Before each test, we create a new instance of the component and the service
  beforeEach(() => {
    fixture = TestBed.createComponent(HeaderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  // Test pour vérifier que le composant est bien créé
  // Test to check if the component is created
  it('should create', () => {
    expect(component).toBeTruthy();
  });
});