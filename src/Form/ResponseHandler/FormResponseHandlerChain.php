<?php

/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Drupal\civiremote_funding\Form\ResponseHandler;

use Drupal\civiremote_funding\Api\Form\FormSubmitResponse;
use Drupal\Core\Form\FormStateInterface;

/**
 * @codeCoverageIgnore
 */
final class FormResponseHandlerChain implements FormResponseHandlerInterface {

  /**
   * @var array<FormResponseHandlerInterface>
   */
  private array $formResponseHandlers;

  public function __construct(FormResponseHandlerInterface ...$formResponseHandlers) {
    $this->formResponseHandlers = $formResponseHandlers;
  }

  public function handleSubmitResponse(FormSubmitResponse $submitResponse, FormStateInterface $formState): void {
    foreach ($this->formResponseHandlers as $formResponseHandler) {
      $formResponseHandler->handleSubmitResponse($submitResponse, $formState);
    }
  }

}
