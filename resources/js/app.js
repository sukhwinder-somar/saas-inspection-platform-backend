import {
  Livewire,
  Alpine,
} from '../../vendor/livewire/livewire/dist/livewire.esm'

import Tooltip from '@ryangjchandler/alpine-tooltip'
import './filament-fix.js'

Alpine.plugin(Tooltip)

Livewire.start()
