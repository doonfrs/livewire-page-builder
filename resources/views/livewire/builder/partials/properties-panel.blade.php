<div class="h-full overflow-y-auto" x-data>
    <div
        class="sticky top-0 bg-gradient-to-r from-gray-800 to-gray-700 text-white px-4 py-3 border-b border-gray-700 shadow-md">
        <h2 class="text-lg font-medium flex items-center">
            <x-heroicon-o-adjustments-horizontal class="w-5 h-5 mr-2" />
            {{ __('Properties') }}
        </h2>
        <div class="mt-1 text-xs font-mono bg-gray-900/50 rounded px-2 py-1 truncate">
            <span
                x-text="selected.label || (selected.type ? (selected.type === 'row' ? 'Row' : 'Block') : '{{ __('No block selected') }}')"></span>
        </div>
    </div>

    <!-- Empty State -->
    <template x-if="!selected.type">
        <div class="flex flex-col items-center justify-center h-64 text-center p-6">
            <x-heroicon-o-square-3-stack-3d class="w-12 h-12 text-gray-300 mb-3 dark:text-gray-600" />
            <div class="text-gray-500 font-medium dark:text-gray-400">{{ __('No properties available') }}</div>
            <div class="text-gray-400 text-sm mt-1 dark:text-gray-500">
                {{ __('Select a block to view and edit its properties') }}</div>
        </div>
    </template>

    <template x-if="selected.type">
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            <template
                x-for="(group, groupName, groupIndex) in (function() {
				const defs = propDefs[selected.classHash] || [];
				const groups = {};
				const defaults = [];
				for (const p of defs) {
					if (p.group) {
						if (!groups[p.group]) groups[p.group] = { label: p.groupLabel || p.group, columns: p.groupColumns || 1, icon: p.groupIcon || 'heroicon-o-tag', properties: [] };
						groups[p.group].properties.push(p);
					} else {
						defaults.push(p);
					}
				}
				if (defaults.length) {
					groups.general = { label: '{{ __('Block Settings') }}', columns: 1, icon: 'heroicon-o-cog-6-tooth', properties: defaults };
				}
				const ordered = {};
				if (groups.general) { ordered.general = groups.general; delete groups.general; }
				for (const k of Object.keys(groups)) ordered[k] = groups[k];
				return ordered;
			})()"
                :key="groupName">
                <div class="p-4" :class="groupIndex % 2 === 1 ? 'bg-gray-50 dark:bg-gray-800/50' : ''">
                    <div
                        class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 flex items-center dark:text-gray-400">
                        <x-heroicon-o-tag class="w-4 h-4 mr-1" />
                        <span x-text="group.label"></span>
                    </div>
                    <div class="space-y-4"
                        :class="group.columns > 1 ? `grid grid-cols-${group.columns} gap-3 space-y-0` : ''">
                        <template x-for="property in group.properties" :key="property.name">
                            <div class="group">
                                <template x-if="property.type === 'checkbox'">
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                            class="form-checkbox h-5 w-5 text-blue-600 rounded transition duration-150 ease-in-out border-gray-300 focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:ring-offset-gray-800"
                                            :checked="!!selected.props[property.name]"
                                            @change.debounce.200ms="selected.props[property.name] = $event.target.checked; Livewire.dispatch('updateBlockProperty', { rowId: selected.type === 'row' ? selected.id : null, blockId: selected.type === 'block' ? selected.id : null, propertyName: property.name, value: $event.target.checked })" />
                                        <label
                                            class="ml-2 ms-2 text-sm font-medium text-gray-700 cursor-pointer dark:text-gray-300"
                                            x-text="property.label"></label>
                                    </div>
                                </template>

                                <template x-if="property.type === 'select'">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-300"><span
                                                x-text="property.label"></span></label>
                                        <select
                                            class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
                                            :value="selected.props[property.name] ?? property.defaultValue ?? ''"
                                            @change.debounce.200ms="selected.props[property.name] = $event.target.value; Livewire.dispatch('updateBlockProperty', { rowId: selected.type === 'row' ? selected.id : null, blockId: selected.type === 'block' ? selected.id : null, propertyName: property.name, value: $event.target.value })">
                                            <option value="" x-show="!property.defaultValue">—</option>
                                            <template
                                                x-for="[val, label] in Object.entries(Array.isArray(property.options) ? (property.options[0] || {}) : (property.options || {}))"
                                                :key="val">
                                                <option :value="val" x-text="label"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>

                                <template
                                    x-if="['checkbox','select','image','color','richtext'].indexOf(property.type) === -1">
                                    <div>
                                        <label
                                            class="flex justify-between text-sm font-medium text-gray-700 mb-1 dark:text-gray-300"><span
                                                x-text="property.label"></span></label>
                                        <input :type="property.numeric ? 'number' : 'text'" :min="property.min ?? null"
                                            :max="property.max ?? null"
                                            class="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
                                            :value="selected.props[property.name] ?? property.defaultValue ?? ''"
                                            @input.debounce.300ms="selected.props[property.name] = $event.target.value; Livewire.dispatch('updateBlockProperty', { rowId: selected.type === 'row' ? selected.id : null, blockId: selected.type === 'block' ? selected.id : null, propertyName: property.name, value: $event.target.value })" />
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </template>
</div>
