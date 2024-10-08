export class CheckboxFilter {
    id: number;
    name: string;
    isChecked: boolean;
  
    constructor(id: number, name: string, isChecked: boolean) {
      this.id = id;
      this.name = name;
      this.isChecked = isChecked;
    }
  }