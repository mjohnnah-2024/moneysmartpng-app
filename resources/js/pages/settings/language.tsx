import { Form, Head } from '@inertiajs/react';
import { Transition } from '@headlessui/react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import LanguageController from '@/actions/App/Http/Controllers/Settings/LanguageController';

export default function Language({ currentLanguage }: { currentLanguage: 'en' | 'tpi' }) {
    return (
        <>
            <Head title="Language settings" />

            <h1 className="sr-only">Language settings</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Language"
                    description="Choose your preferred language"
                />

                <Form
                    {...LanguageController.update.form({ preferred_language: currentLanguage })}
                    options={{ preserveScroll: true }}
                    className="space-y-6"
                >
                    {({ processing, recentlySuccessful, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="preferred_language">Preferred language</Label>
                                <select
                                    id="preferred_language"
                                    name="preferred_language"
                                    defaultValue={currentLanguage}
                                    className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                >
                                    <option value="en">English</option>
                                    <option value="tpi">Tok Pisin</option>
                                </select>
                                {errors.preferred_language && (
                                    <p className="text-sm text-red-600">{errors.preferred_language}</p>
                                )}
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>Save</Button>

                                <Transition
                                    show={recentlySuccessful}
                                    enter="transition ease-in-out"
                                    enterFrom="opacity-0"
                                    leave="transition ease-in-out"
                                    leaveTo="opacity-0"
                                >
                                    <p className="text-sm text-neutral-600">Saved.</p>
                                </Transition>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}
