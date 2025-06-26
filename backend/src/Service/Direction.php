<?php

namespace App\Service;
/*
 * Direction enum defines the possible directions for moving entities in a list.
 * It includes:
 * - Top: Move to the top of the list.
 * - Up: Move one position up in the list.
 * - Down: Move one position down in the list.
 * - Bottom: Move to the bottom of the list.
 */
enum Direction
{
    case Top;
    case Up;
    case Down;
    case Bottom;
}