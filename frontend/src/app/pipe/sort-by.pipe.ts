import {Pipe, PipeTransform} from '@angular/core';

// Pipe to sort data
@Pipe({
  name: 'sortBy',
  standalone: true,
  pure: false 
})

export class SortPipe implements PipeTransform {
  transform(array: Array<any> | null, args: any): Array<any> | null {
    if (array === null || array === undefined || array.length === 0 || typeof args !== 'string') {
      return array;
    }

    const keys = [args];
    const order = 1;

    array.sort((a: any, b: any) => {
      if (a[keys[0]] < b[keys[0]]) {
        return -1 * order;
      } else if (a[keys[0]] > b[keys[0]]) {
        return 1 * order;
      } else {
        return 0;
      }
    });

    return array;
  }
}